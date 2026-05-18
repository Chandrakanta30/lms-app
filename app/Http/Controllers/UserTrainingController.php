<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TrainingModule;
use App\Models\UserTraining;
use DB;
class UserTrainingController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Base Trainee Query
        |--------------------------------------------------------------------------
        */
        $traineesQuery = User::
            with([
                'department',
                'trainings' => function ($query) {
                    $query->whereIn('training_user.status', ['enrolled', 'pending'])
                    ->where('name', 'Induction Training')
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
        $trainees = $trainees->map(function ($user) use ($completedTrainings) {

            $completedModuleIds = collect($completedTrainings[$user->id] ?? [])
                ->pluck('training_module_id')
                ->toArray();

            $user->assigned_progress = $user->trainings->map(function ($training) use ($completedModuleIds) {

                // Parent Module
                if (is_null($training->parent_id)) {

                    $stepIds = $training->steps->pluck('id')->toArray();

                    $totalSteps = count($stepIds);

                    $completedCount = count(
                        array_intersect($stepIds, $completedModuleIds)
                    );

                } else {

                    // Single Step
                    $totalSteps = 1;

                    $completedCount = in_array(
                        $training->id,
                        $completedModuleIds
                    ) ? 1 : 0;
                }

                $percent = $totalSteps > 0
                    ? round(($completedCount / $totalSteps) * 100)
                    : 0;

                return [
                    'id'        => $training->id,
                    'name'      => $training->name,
                    'completed' => $completedCount,
                    'total'     => $totalSteps,
                    'percent'   => $percent,
                    'status'    => $percent == 100
                        ? 'Completed'
                        : ($percent > 0 ? 'In Progress' : 'Enrolled'),

                    'color'     => $percent == 100
                        ? 'success'
                        : ($percent > 0 ? 'warning' : 'info'),

                    'steps' => $training->steps->map(function ($step) use ($completedModuleIds) {

                        $words = preg_split('/[\s\-]+/', trim($step->name));

                        $ignoreWords = ['and', 'of', 'the', 'for', 'to'];

                        $code = collect($words)
                            ->reject(fn ($word) => in_array(strtolower($word), $ignoreWords))
                            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                            ->implode('&');


                    
                        return [
                            'id'            => $step->id,
                            'name'          => $step->name,
                            'short_code'    => $code,
                            'color'         => $step->color,
                            'is_completed'  => in_array($step->id, $completedModuleIds),
                            // 'completed_at'  => $step->activated_at,
                        ];
                    }),
                ];
            });

            return $user;
        });

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
            ->groupBy(fn ($user) => $user->department->name ?? 'Unassigned')
            ->map(function ($users, $departmentName) {

                $allProgress = $users->flatMap->assigned_progress;

                return [
                    'department'  => $departmentName,
                    'users'       => $users->count(),
                    'pending'     => $allProgress->where('percent', 0)->count(),
                    'in_progress' => $allProgress
                        ->filter(fn ($item) =>
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

            // return $trainees;
        return view(
            'user_trainings.index',
            compact('trainees', 'departmentBreakdown')
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

    \Illuminate\Support\Facades\DB::table('user_trainings')->updateOrInsert(
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
    $currentStep = \App\Models\TrainingModule::find($request->module_id);

    // 2. Get parent training (main program)
    $parentTraining = $currentStep->parent;

    // If no parent, it means this is parent itself
    if (!$parentTraining) {
        $parentTraining = $currentStep;
    }

    // 3. Get all steps under this training
    $stepIds = \App\Models\TrainingModule::where('parent_id', $parentTraining->id)
        ->pluck('id')
        ->toArray();

    // 4. Count completed steps
    $completedCount = \Illuminate\Support\Facades\DB::table('user_trainings')
        ->where('user_id', $user->id)
        ->whereIn('training_module_id', $stepIds)
        ->where('is_completed', 1)
        ->count();

    // 5. If ALL steps completed → update role
    if (
    count($stepIds) > 0 &&
    $completedCount === count($stepIds) &&
    strtolower($parentTraining->name) === 'induction training'
) {

    // 🔥 CHANGE ROLE (Trainee → Employee)
    $user->assignRole(['Employee']);

    // Optional: flash message
    session()->flash('success', 'User promoted to Regular (Employee)');
}



    return back()->with('success', 'Step Logged!');
}







    public function show(User $user, TrainingModule $training)
    {
        if (auth()->user()?->hasRole('Trainee') && auth()->id() !== $user->id) {
            abort(403, 'You are not allowed to view another trainee\'s training.');
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
        $completedIds = \Illuminate\Support\Facades\DB::table('user_trainings')
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

    $userLogs = \Illuminate\Support\Facades\DB::table('user_trainings')
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
}
