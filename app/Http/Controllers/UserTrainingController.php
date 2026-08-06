<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TrainingModule;
use App\Models\UserTraining;
use App\Services\TrainingWorkflowService;

class UserTrainingController extends Controller
{
    public function __construct(private TrainingWorkflowService $workflow)
    {
    }

    /**
     * Progress list for one parent program.
     *
     * @param  string|null  $program  induction | glp | functional (defaults to induction)
     */
    public function index(?string $program = null)
    {
        $currentUser = auth()->user();
        $programSlug = $this->workflow->resolveSlug($program);

        /*
        |--------------------------------------------------------------------------
        | Base Trainee Query
        |--------------------------------------------------------------------------

        */
        $traineesQuery = User::with([
            'department',
            'trainings' => function ($query) {
                $query->whereIn('training_user.status', ['enrolled', 'pending'])
                    ->whereNull('training_modules.parent_id')
                    ->whereIn(
                        DB::raw('LOWER(training_modules.name)'),
                        $this->workflow->lowerCasedLabels()
                    )
                    ->with('steps');
            }
        ]);

        // If logged-in user is a trainee, only show their data
        if ($currentUser && $currentUser->hasRole('Trainee')) {
            $traineesQuery->whereKey($currentUser->id);
        }

        $trainees = $traineesQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Get Completed Trainings in One Query (Avoid N+1)
        |--------------------------------------------------------------------------
        */
        $completedTrainings = DB::table('user_trainings')
            ->whereIn('user_id', $trainees->pluck('id'))
            ->where('is_completed', true)
            ->get()
            ->groupBy('user_id');

        /*
        |--------------------------------------------------------------------------
        | Process User Progress
        |--------------------------------------------------------------------------
        */
        $trainees = $trainees->map(function ($user) use ($completedTrainings, $programSlug) {

            $completedModuleIds = collect($completedTrainings[$user->id] ?? [])
                ->pluck('training_module_id')
                ->toArray();

            // Progress for all three programs, keyed by slug.
            $allProgress = $this->workflow->progressForPrograms($user->trainings, $completedModuleIds);

            $user->all_progress = $allProgress;
            $user->is_locked = ! $this->workflow->isProgramAccessible($programSlug, $allProgress);
            $user->locked_reason = $this->workflow->lockedReason($programSlug);

            $user->assigned_progress = $allProgress->has($programSlug)
                ? collect([$allProgress->get($programSlug)])
                : collect();

            return $user;
        })
        // Users not enrolled in this program have nothing to show.
        ->filter(fn ($user) => $user->assigned_progress->isNotEmpty())
        ->values();

        /*
        |--------------------------------------------------------------------------
        | Sort Users by Lowest Progress
        |--------------------------------------------------------------------------
        */
        $trainees = $trainees
            ->sortBy(function ($user) {
                return collect($user->assigned_progress)->min('percent') ?? 0;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Department Breakdown
        |--------------------------------------------------------------------------
        */
        $departmentBreakdown = $trainees
            ->groupBy(fn($user) => $user->department->name ?? 'Unassigned')
            ->map(function ($users, $departmentName) {

                $allProgress = $users->flatMap->assigned_progress;

                return [
                    'department'  => $departmentName,
                    'users'       => $users->count(),
                    'pending'     => $allProgress->where('percent', 0)->count(),
                    'in_progress' => $allProgress
                        ->filter(
                            fn($item) =>
                            $item['percent'] > 0 &&
                                $item['percent'] < 100
                        )->count(),

                    'completed'   => $allProgress
                        ->where('percent', 100)
                        ->count(),
                ];
            })
            ->sortByDesc('pending')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Program  (Induction -> GLP -> Functional)
        |--------------------------------------------------------------------------
        */
        $programTabs = collect($this->workflow->slugs())->map(fn (string $slug) => [
            'slug'      => $slug,
            'label'     => $this->workflow->label($slug),
            'url'       => route('user.training.index', ['program' => $slug]),
            'is_active' => $slug === $programSlug,
        ]);

        $programLabel = $this->workflow->label($programSlug);

        return view(
            'user_trainings.index',
            compact('trainees', 'departmentBreakdown', 'programSlug', 'programLabel', 'programTabs')
        );
    }

    // Show specific user's training checklist
    // public function show(User $user)
    // {
    //     $programs = TrainingModule::whereNull('parent_id')->with('steps')->get();
    //     $completedIds = $user->trainings()->pluck('training_module_id')->toArray();

    //     return view('user_trainings.show', compact('user', 'programs', 'completedIds'));
    // }



    // making some changes here to log the interaction details in the user_trainings table instead of just marking it as completed. This way, we can capture who interacted with the trainee, their designation, and any comments about the interaction.

    // // Log the interaction
    public function store(Request $request, User $user, TrainingModule $training)
    {
        if (auth()->user()?->hasRole('Trainee') && auth()->id() !== $user->id) {
            abort(403, 'You are not allowed to update another trainee\'s progress.');
        }

        DB::table('user_trainings')->updateOrInsert(
            ['user_id' => $user->id, 'training_module_id' => $request->module_id],
            [
                'interacted_person' => $request->interacted_person,
                'designation'       => $request->designation,
                'comments'          => $request->comments,
                'is_completed'      => 1,
                'updated_at'        => now()
            ]
        );

        // 1. Get current step
        $currentStep = TrainingModule::find($request->module_id);

        // 2. Get parent training (main program). If there is no parent, this is
        //    the parent itself.
        $parentTraining = $currentStep->parent ?? $currentStep;

        // 3. Get all steps under this training
        $stepIds = TrainingModule::where('parent_id', $parentTraining->id)
            ->pluck('id')
            ->toArray();

        // 4. Count completed steps
        $completedCount = DB::table('user_trainings')
            ->where('user_id', $user->id)
            ->whereIn('training_module_id', $stepIds)
            ->where('is_completed', 1)
            ->count();

        // 5. If ALL steps completed → update role
        $programComplete = count($stepIds) > 0 && $completedCount === count($stepIds);

        if (
            $programComplete
            && $this->workflow->slugForName($parentTraining->name) === TrainingWorkflowService::INDUCTION
        ) {
            // CHANGE ROLE (Trainee → Regular)
            $user->syncRoles(['Regular']);

            session()->flash('success', 'User promoted from Trainee to Regular');
        }

        return back()->with('success', 'Step Logged!');
    }







    public function show(User $user, TrainingModule $training)
    {
        if (auth()->user()?->hasRole('Trainee') && auth()->id() !== $user->id) {
            abort(403, 'You are not allowed to view another trainee\'s training.');
        }

        
        $slug = $this->workflow->slugForName($training->name);

        if ($slug !== null && ! $this->workflow->isProgramAccessible($slug, $this->programProgressFor($user))) {
            return redirect()
                ->route('user.training.index', ['program' => TrainingWorkflowService::INDUCTION])
                ->with('error', $this->workflow->lockedReason($slug));
        }

        $loggedInUser = auth()->user()?->loadMissing('designation');

        /**
         * 1. $training is the Parent Module (Program) assigned to the user.
         * We load all child steps belonging to this parent module.
         */
        $program = $training->load(['steps' => function ($query) {
            $query->orderBy('step_number', 'asc');
        }]);

        /**
         * 2. Fetch the IDs of all individual steps the user has finished.
         * These IDs come from your 'user_trainings' table where 'is_completed' is true.
         */
        $completedIds = DB::table('user_trainings')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('training_module_id') // This captures the individual step IDs
            ->toArray();

        $interactionDefaults = [
            'interacted_person' => $loggedInUser?->name ?? '',
            'designation' => $loggedInUser?->designation?->name ?? '',
            'comments' => 'Training step reviewed and explained to the trainee. User demonstrated understanding and the completion was recorded.',
        ];

        /**
         * 3. Return the view with:
         * - The User (Trainee)
         * - The Parent Module (with its child steps)
         * - The array of finished step IDs for the Blade's in_array() check
         */
        return view('user_trainings.show', compact('user', 'program', 'completedIds', 'interactionDefaults'));
    }

    // public function store(Request $request, User $user, TrainingModule $training)
    // {
    //     $request->validate([
    //         'step_id'      => 'required|exists:training_modules,id',
    //         'is_completed' => 'required|boolean',
    //     ]);

    //     // Update the specific step in the pivot table
    //     $user->trainings()->updateExistingPivot($request->step_id, [
    //         'is_completed' => $request->is_completed,
    //         'status'       => $request->is_completed ? 'completed' : 'in_progress',
    //         'updated_at'   => now(),
    //     ]);

    //     return redirect()->back()->with('success', 'Progress updated!');
    // }

    public function report(User $user, $training_id)
    {
        if (auth()->user()?->hasRole('Trainee') && auth()->id() !== $user->id) {
            abort(403, 'You are not allowed to view another trainee\'s report.');
        }

        // 1. Find the Parent Program and all its Child Steps
        $trainingProgram = TrainingModule::where('id', $training_id)
            ->whereNull('parent_id')
            ->with(['steps' => function ($query) {
                $query->orderBy('step_number', 'asc');
            }])
            ->firstOrFail();

        // 2. Fetch the Step IDs belonging to this program
        $stepIds = $trainingProgram->steps->pluck('id')->toArray();

        $userLogs = DB::table('user_trainings')
            ->where('user_id', $user->id)
            ->whereIn('training_module_id', $stepIds)
            ->where('is_completed', true)
            ->get()
            ->map(function ($log) {
                // We create a generic object to mimic a Model with a 'pivot' relation
                return (object) [
                    'id' => $log->training_module_id,
                    'pivot' => (object) [
                        'interacted_person' => $log->interacted_person,
                        'designation'       => $log->designation,
                        'comments'          => $log->comments,
                        'completed_at'      => $log->updated_at // Mapping updated_at to completed_at for Blade
                    ]
                ];
            });

        /**
         * The Blade uses: $userLogs->where('id', $step->id)->first()
         * Our mapped collection above now supports this exactly.
         */
        return view('user_trainings.report', compact('user', 'trainingProgram', 'userLogs'));
    }


    public function certificate(User $user)
    {
        if (auth()->user()?->hasRole('Trainee') && auth()->id() !== $user->id) {
            abort(403, 'You are not allowed to view another trainee\'s certificate.');
        }

        $user->loadMissing(['department', 'designation']);

        $progress = $this->programProgressFor($user);

        $isEligible = $this->workflow->hasCompletedAllPrograms($progress);
        $rows = $this->workflow->certificateRows($progress);
        $message = $this->workflow->certificateMessage($progress);

        return view('user_trainings.certificate', compact('user', 'rows', 'isEligible', 'message'));
    }

    // Progress across Induction / GLP / Functional for a single user
    private function programProgressFor(User $user): Collection
    {
        $completedIds = DB::table('user_trainings')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('training_module_id')
            ->toArray();

        $programs = $user->trainings()
            ->whereNull('training_modules.parent_id')
            ->whereIn(DB::raw('LOWER(training_modules.name)'), $this->workflow->lowerCasedLabels())
            ->with('steps')
            ->get();

        return $this->workflow->progressForPrograms($programs, $completedIds);
    }
}
