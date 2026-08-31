<?php

namespace App\Services;

use App\Models\DocumentReadTracker;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Support\Collection;


class TrainingWorkflowService
{
    public const INDUCTION = 'induction';
    public const GLP = 'glp';
    public const FUNCTIONAL = 'functional';


    public const PROGRAMS = [
        self::INDUCTION  => 'Induction Training',
        self::GLP        => 'GLP Training',
        self::FUNCTIONAL => 'Functional Training',
    ];

    /*
    |--------------------------------------------------------------------------
    | Assessment stages
    |--------------------------------------------------------------------------
    | A trainee walks enrol -> attendance -> read documents -> exam -> pass.
    | Steps may only be logged once that walk has reached STAGE_READY.
    */
    public const STAGE_NOT_ENROLLED  = 'not_enrolled';
    public const STAGE_NO_ASSESSMENT = 'no_assessment';
    public const STAGE_ATTENDANCE   = 'attendance';
    public const STAGE_READING      = 'reading';
    public const STAGE_EXAM         = 'exam';
    public const STAGE_FAILED       = 'failed';
    public const STAGE_READY        = 'ready';

    /** Per-request cache so a progress list does not re-query the same pair. */
    private array $eligibilityCache = [];

    // Step short codes that must not be derived from initials. 
    private const SHORT_CODE_OVERRIDES = [
        'administration and maintenance:' => 'AMD',
        'development quality assurance'   => 'DQA',
        'analytical services'             => 'ASD',
    ];

    private const SHORT_CODE_STOP_WORDS = ['and', 'of', 'the', 'for', 'to'];

    public function slugs(): array
    {
        return array_keys(self::PROGRAMS);
    }

    public function label(string $slug): string
    {
        return self::PROGRAMS[$slug] ?? self::PROGRAMS[self::INDUCTION];
    }

    // Normalise anything the router hands us to a known slug. 
    public function resolveSlug(?string $slug): string
    {
        $slug = strtolower(trim((string) $slug));

        return array_key_exists($slug, self::PROGRAMS) ? $slug : self::INDUCTION;
    }


    public function slugForName(?string $name): ?string
    {
        $name = strtolower(trim((string) $name));

        foreach (self::PROGRAMS as $slug => $label) {
            $pattern = '/^' . preg_quote(strtolower($label), '/') . '[\s\-_]*\d*$/';

            if (preg_match($pattern, $name)) {
                return $slug;
            }
        }

        return null;
    }


    public function lowerCasedLabels(): array
    {
        return array_map('strtolower', array_values(self::PROGRAMS));
    }


    public function nameLikePatterns(): array
    {
        return array_map(
            fn (string $label) => strtolower($label) . '%',
            array_values(self::PROGRAMS)
        );
    }

   
     // Progress for every parent program the user is enrolled in, keyed by slug
     // and ordered Induction -> GLP -> Functional.
  
    public function progressForPrograms(Collection $programs, array $completedModuleIds, ?User $user = null): Collection
    {
        $completedModuleIds = array_map('intval', $completedModuleIds);

       
        $bySlug = $programs
            ->map(fn (TrainingModule $program) => $this->progressForProgram($program, $completedModuleIds, $user))
            ->filter(fn (?array $progress) => $progress !== null)
            ->groupBy('slug');

        // Re-order to the canonical Induction -> GLP -> Functional sequence.
        return collect($this->slugs())
            ->filter(fn (string $slug) => $bySlug->has($slug))
            ->mapWithKeys(fn (string $slug) => [
                $slug => $this->aggregate($slug, $bySlug->get($slug)->values()),
            ]);
    }

    /**
     * Roll several trainings of one programme into a single progress entry.
     *
     * @param  Collection<int, array>  $trainings  per-training progress rows
     */
    private function aggregate(string $slug, Collection $trainings): array
    {
        $completed = (int) $trainings->sum('completed');
        $total = (int) $trainings->sum('total');
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Complete only when every training for this programme is complete
        // (all steps logged AND its assessment passed).
        $isCompleted = $trainings->isNotEmpty()
            && $trainings->every(fn (array $training) => $training['is_completed']);

        $canLogSteps = $trainings->isNotEmpty()
            && $trainings->every(fn (array $training) => $training['can_log_steps']);

        $blockedReason = (string) ($trainings
            ->first(fn (array $training) => $training['blocked_reason'] !== '')['blocked_reason'] ?? '');

        $first = $trainings->first();

        [$status, $color] = match (true) {
            $isCompleted   => ['Completed', 'success'],
            ! $canLogSteps => ['Assessment Pending', 'warning'],
            $percent > 0   => ['In Progress', 'warning'],
            default        => ['Enrolled', 'info'],
        };

        return [
            'slug'           => $slug,
            'id'             => $first['id'] ?? null,
            'name'           => $first['name'] ?? $this->label($slug),
            'label'          => $this->label($slug),
            'completed'      => $completed,
            'total'          => $total,
            'percent'        => $percent,
            'is_completed'   => $isCompleted,
            'can_log_steps'  => $canLogSteps,
            'blocked_reason' => $blockedReason,
            'status'         => $status,
            'status_label'   => $status,
            'color'          => $color,
            'steps'          => $trainings->flatMap(fn (array $training) => $training['steps'])->values(),
            'trainings'      => $trainings,
        ];
    }

    /**
     * Progress for a single parent program, or null if it is not one of the three.
     *
     * When $user is given the programme is also gated on the assessment: the
     * steps only count as a finished programme once the trainee has passed the
     * programme's exam. Passing $user as null keeps the old, ungated behaviour.
     */
    public function progressForProgram(TrainingModule $program, array $completedModuleIds, ?User $user = null): ?array
    {
        $slug = $this->slugForName($program->name);

        if ($slug === null) {
            return null;
        }

        $completedModuleIds = array_map('intval', $completedModuleIds);

        $steps = $program->steps ?? collect();
        $stepIds = $steps->pluck('id')->map(fn ($id) => (int) $id)->all();

        $total = count($stepIds);
        $completed = count(array_intersect($stepIds, $completedModuleIds));
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $eligibility = $user ? $this->eligibility($program, $user) : null;
        $assessmentCleared = $eligibility === null ? true : $eligibility['can_log_steps'];

        $stepsCompleted = $total > 0 && $completed === $total;
        $isCompleted = $stepsCompleted && $assessmentCleared;

        [$status, $color] = match (true) {
            $isCompleted                           => ['Completed', 'success'],
            $stepsCompleted && ! $assessmentCleared => ['Assessment Pending', 'warning'],
            ! $assessmentCleared                   => ['Awaiting Assessment', 'secondary'],
            $percent > 0                           => ['In Progress', 'warning'],
            default                                => ['Enrolled', 'info'],
        };

        return [
            'slug'            => $slug,
            'id'              => $program->id,
            'name'            => $program->name,
            'label'           => $this->label($slug),
            'completed'       => $completed,
            'total'           => $total,
            'percent'         => $percent,
            'steps_completed' => $stepsCompleted,
            'exam_passed'     => $eligibility === null ? true : $eligibility['exam_passed'],
            'exam_configured' => $eligibility === null ? true : $eligibility['exam_configured'],
            'can_log_steps'   => $assessmentCleared,
            'exam_stage'      => $eligibility['stage'] ?? self::STAGE_READY,
            'blocked_reason'  => $assessmentCleared ? '' : $eligibility['reason'],
            'is_completed'    => $isCompleted,
            'status'          => $status,
            'status_label'    => $status,
            'color'           => $color,
            'steps'           => $steps->map(fn (TrainingModule $step) => [
                'id'           => $step->id,
                'name'         => $step->name,
                'short_code'   => $this->shortCode($step->name),
                'color'        => $step->color ?? null,
                'is_completed' => in_array((int) $step->id, $completedModuleIds, true),
            ])->values(),
        ];
    }


    public function isProgramAccessible(string $slug, Collection $progress): bool
    {
        if ($slug === self::INDUCTION) {
            return true;
        }

        return $this->hasCompleted(self::INDUCTION, $progress);
    }

    public function hasCompleted(string $slug, Collection $progress): bool
    {
        return (bool) data_get($progress->get($slug), 'is_completed', false);
    }

    /** True only when Induction, GLP and Functional are all finished. */
    public function hasCompletedAllPrograms(Collection $progress): bool
    {
        foreach ($this->slugs() as $slug) {
            if (! $this->hasCompleted($slug, $progress)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Message shown when the certificate is not yet earned.
     * Returns an empty string once all three programs are complete.
     */
    public function certificateMessage(Collection $progress): string
    {
        $induction  = $this->hasCompleted(self::INDUCTION, $progress);
        $glp        = $this->hasCompleted(self::GLP, $progress);
        $functional = $this->hasCompleted(self::FUNCTIONAL, $progress);

        return match (true) {
            $induction && $glp && $functional   => '',
            $induction && $glp && ! $functional => 'Complete Functional Training to receive your Training Certificate.',
            $induction && $functional && ! $glp => 'Complete GLP Training to receive your Training Certificate.',
            $induction                          => 'Complete GLP Training and Functional Training to receive your Training Certificate.',
            default                             => 'Complete all required trainings to receive your Training Certificate.',
        };
    }

  
    public function certificateRows(Collection $progress): Collection
    {
        return collect($this->slugs())->map(function (string $slug) use ($progress) {
            $program = $progress->get($slug);

            return [
                'slug'         => $slug,
                'label'        => $this->label($slug),
                'is_completed' => (bool) data_get($program, 'is_completed', false),
                'completed'    => (int) data_get($program, 'completed', 0),
                'total'        => (int) data_get($program, 'total', 0),
            ];
        })->values();
    }

    /** Reason a locked program cannot be opened yet. */
    public function lockedReason(string $slug): string
    {
        return 'Complete Induction Training to unlock ' . $this->label($slug) . '.';
    }

    /**
     * Where a trainee stands on one parent programme's assessment walk:
     * enrol -> attendance -> read documents -> attempt exam -> pass.
     *
     * Steps may only be logged once that walk is finished, so this is the one
     * place that decides it. The exam outcome itself is not recomputed here --
     * TrainingModule::resolveTrainingStatusForUser() stays the authority, so
     * reassignment, deadlines and the cached pivot status keep their meaning.
     *
     * @return array{enrolled:bool, attendance_marked:bool, reading_completed:bool,
     *               exam_attempted:bool, exam_passed:bool, exam_required:bool,
     *               exam_status:string, can_log_steps:bool, stage:string, reason:string}
     */
    public function eligibility(TrainingModule $program, User $user): array
    {
        $cacheKey = $program->id . ':' . $user->id;

        if (isset($this->eligibilityCache[$cacheKey])) {
            return $this->eligibilityCache[$cacheKey];
        }

        $assignment = $program->currentAssignmentForUser($user);
        $enrolled = $assignment !== null;

        // Whether an exam can actually be sat: at least one linked document that
        // is reviewed, carries questions and has a quota. When this is false the
        // programme is mis-configured -- the trainee cannot reach the exam, so
        // the steps stay locked and the reason says what the admin must fix.
        $examConfigured = $program->examDocuments()->exists();

        $attendanceMarked = $enrolled && ($assignment->attendance_status ?? null) === 'present';

        $readingCompleted = $enrolled && DocumentReadTracker::query()
            ->where('user_id', $user->id)
            ->where('training_module_id', $program->id)
            ->whereNotNull('completed_at')
            ->exists();

        $examStatus = $enrolled ? $program->resolveTrainingStatusForUser($user) : 'pending';
        $examAttempted = $enrolled && $program->latestResultForUser($user) !== null;
        $examPassed = $examStatus === 'passed';

        // The exam pass is mandatory. Enrolment alone never unlocks the steps.
        $canLogSteps = $enrolled && $examPassed;

        $stage = match (true) {
            ! $enrolled              => self::STAGE_NOT_ENROLLED,
            $examPassed              => self::STAGE_READY,
            ! $examConfigured        => self::STAGE_NO_ASSESSMENT,
            $examStatus === 'failed' => self::STAGE_FAILED,
            ! $attendanceMarked      => self::STAGE_ATTENDANCE,
            ! $readingCompleted      => self::STAGE_READING,
            default                  => self::STAGE_EXAM,
        };

        $eligibility = [
            'enrolled'          => $enrolled,
            'attendance_marked' => $attendanceMarked,
            'reading_completed' => $readingCompleted,
            'exam_attempted'    => $examAttempted,
            'exam_passed'       => $examPassed,
            'exam_configured'   => $examConfigured,
            'exam_status'       => $examStatus,
            'can_log_steps'     => $canLogSteps,
            'stage'             => $stage,
            'reason'            => $this->stageReason($stage, $program->name),
        ];

        return $this->eligibilityCache[$cacheKey] = $eligibility;
    }

    /** Human-readable explanation of why steps cannot be logged yet. */
    public function stageReason(string $stage, string $programName): string
    {
        return match ($stage) {
            self::STAGE_NOT_ENROLLED  => 'This trainee is not enrolled in ' . $programName . '.',
            self::STAGE_NO_ASSESSMENT => 'No assessment is available for ' . $programName . ' yet. Link a reviewed document that has questions and a question quota before steps can be logged.',
            self::STAGE_ATTENDANCE   => 'Attendance for ' . $programName . ' has not been marked yet.',
            self::STAGE_READING      => 'The required documents for ' . $programName . ' have not been read yet.',
            self::STAGE_EXAM         => 'The assessment for ' . $programName . ' has not been passed yet.',
            self::STAGE_FAILED       => 'The assessment for ' . $programName . ' was not passed. Reassign the training so the trainee can retake it.',
            default                  => '',
        };
    }

    /** Ordered checklist of the assessment walk, for display. */
    public function eligibilityChecklist(array $eligibility): array
    {
        return [
            ['label' => 'Enrolled in the training',  'done' => $eligibility['enrolled']],
            ['label' => 'Attendance marked present', 'done' => $eligibility['attendance_marked']],
            ['label' => 'Required documents read',   'done' => $eligibility['reading_completed']],
            ['label' => 'Assessment attempted',      'done' => $eligibility['exam_attempted']],
            ['label' => 'Assessment passed',         'done' => $eligibility['exam_passed']],
        ];
    }

    private function shortCode(string $name): string
    {
        $key = strtolower(trim($name));

        if (isset(self::SHORT_CODE_OVERRIDES[$key])) {
            return self::SHORT_CODE_OVERRIDES[$key];
        }

        return collect(preg_split('/[\s\-]+/', trim($name)))
            ->reject(fn (string $word) => in_array(strtolower($word), self::SHORT_CODE_STOP_WORDS, true))
            ->map(fn (string $word) => strtoupper(substr($word, 0, 1)))
            ->implode('');
    }
}
