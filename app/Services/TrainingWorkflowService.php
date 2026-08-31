<?php

namespace App\Services;

use App\Models\TrainingModule;
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

    /**
     * Map a training_modules.name back to its slug (null if not one of the three).
     *
     * A trailing number makes a numbered variant of the same programme, so
     * "Induction Training", "Induction Training 2" and "Induction Training 3"
     * all resolve to the 'induction' slug. Separators (-, _, space) and case
     * are ignored, but the label itself must match in full: "induction9" or
     * "Induction Refresher" deliberately do NOT match.
     */
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

    /**
     * LOWER(name) LIKE patterns that capture a programme and its numbered
     * variants ("induction training", "induction training 2", ...).
     *
     * Deliberately a superset: the SQL narrows the rows, then slugForName()
     * decides precisely which of them really belong to a programme.
     */
    public function nameLikePatterns(): array
    {
        return array_map(
            fn (string $label) => strtolower($label) . '%',
            array_values(self::PROGRAMS)
        );
    }

   
     // Progress for every parent program the user is enrolled in, keyed by slug
     // and ordered Induction -> GLP -> Functional.
  
    public function progressForPrograms(Collection $programs, array $completedModuleIds): Collection
    {
        $completedModuleIds = array_map('intval', $completedModuleIds);

        // A user may be enrolled in several numbered trainings of the same
        // programme (Induction Training, Induction Training 2, ...). Group them
        // by slug and aggregate, so the programme only counts as complete when
        // EVERY training the user holds for that slug is complete.
        $bySlug = $programs
            ->map(fn (TrainingModule $program) => $this->progressForProgram($program, $completedModuleIds))
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

        // Complete only when every training for this programme is complete.
        $isCompleted = $trainings->isNotEmpty()
            && $trainings->every(fn (array $training) => $training['is_completed']);

        $first = $trainings->first();

        return [
            'slug'         => $slug,
            'id'           => $first['id'] ?? null,
            'name'         => $first['name'] ?? $this->label($slug),
            'label'        => $this->label($slug),
            'completed'    => $completed,
            'total'        => $total,
            'percent'      => $percent,
            'is_completed' => $isCompleted,
            'status'       => $isCompleted ? 'Completed' : ($percent > 0 ? 'In Progress' : 'Enrolled'),
            'color'        => $isCompleted ? 'success' : ($percent > 0 ? 'warning' : 'info'),
            'steps'        => $trainings->flatMap(fn (array $training) => $training['steps'])->values(),
            'trainings'    => $trainings,
        ];
    }

    /** Progress for a single parent program, or null if it is not one of the three. */
    public function progressForProgram(TrainingModule $program, array $completedModuleIds): ?array
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

        return [
            'slug'         => $slug,
            'id'           => $program->id,
            'name'         => $program->name,
            'label'        => $this->label($slug),
            'completed'    => $completed,
            'total'        => $total,
            'percent'      => $percent,
            'is_completed' => $total > 0 && $completed === $total,
            'status'       => $percent === 100 ? 'Completed' : ($percent > 0 ? 'In Progress' : 'Enrolled'),
            'color'        => $percent === 100 ? 'success' : ($percent > 0 ? 'warning' : 'info'),
            'steps'        => $steps->map(fn (TrainingModule $step) => [
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
