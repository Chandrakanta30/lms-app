<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TrainingModule;

class UserTrainingController extends Controller
{
    public function index()
    {
       // 1. Get all Parent Modules with their child steps
    $mainModules = TrainingModule::whereNull('parent_id')->with('steps')->get();
    $currentUser = auth()->user();

    // 2. Fetch Trainees and their completed training records
    // $trainees = User::role('Trainee')
    // ->with(['trainings' => function ($query) {
    //     // $query->wherePivot('status', 'enrolled');
    //     $query->whereIn('training_user.status', ['enrolled', 'pending']);
    // }])
    // ->get()
    // ->map(function ($user) {
    //     // Group assigned trainings by their parent_id to handle modules with steps
    //     // Note: If training is a parent, parent_id is null.
    //     $user->assigned_progress = $user->trainings->map(function ($training) use ($user) {
            
    //         // If the training is a parent (parent_id is null), 
    //         // we check how many of its steps are completed by this user
    //         if (is_null($training->parent_id)) {
    //             $steps = \App\Models\TrainingModule::where('parent_id', $training->id)->get();
    //             $totalSteps = $steps->count();
                
    //             // Count how many of these steps exist in the user's trainings 
    //             // AND are marked as completed (assuming you have an is_completed flag)
    //             $completedCount = $user->trainings
    //                 ->whereIn('id', $steps->pluck('id'))
    //                 ->where('pivot.is_completed', true) // Adjust 'is_completed' to your actual pivot column name
    //                 ->count();
    //         } else {
    //             // It's a single assigned step
    //             $totalSteps = 1;
    //             $completedCount = $training->pivot->is_completed ? 1 : 0;
    //         }

    //         $percent = $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0;

    //         return [
    //             'id' => $training->id,
    //             'name' => $training->name,
    //             'completed' => $completedCount,
    //             'total' => $totalSteps,
    //             'percent' => $percent,
    //             'status' => $percent == 100 ? 'Completed' : ($percent > 0 ? 'In Progress' : 'Enrolled'),
    //             'color' => $percent == 100 ? 'success' : ($percent > 0 ? 'warning' : 'info')
    //         ];
    //     });

    //     return $user;
    // });
    $traineesQuery = User::role('Trainee');

    if ($currentUser && $currentUser->hasRole('Trainee')) {
        $traineesQuery->whereKey($currentUser->id);
    }

    $trainees = $traineesQuery
        ->with(['trainings' => function ($query) {
            // Get all assigned trainings from the pivot table
            $query->whereIn('training_user.status', ['enrolled', 'pending']);
        }])
        ->get()
        ->map(function ($user) {
            
            // Get the list of completed module IDs from the user_trainings table
            $completedModuleIds = \Illuminate\Support\Facades\DB::table('user_trainings')
                ->where('user_id', $user->id)
                ->where('is_completed', true) // Now checking the column in user_trainings
                ->pluck('training_module_id')
                ->toArray();

            $user->assigned_progress = $user->trainings->map(function ($training) use ($completedModuleIds) {
                
                if (is_null($training->parent_id)) {
                    // Parent Module Logic
                    $stepIds = \App\Models\TrainingModule::where('parent_id', $training->id)->pluck('id')->toArray();
                    $totalSteps = count($stepIds);
                    $completedCount = count(array_intersect($stepIds, $completedModuleIds));
                } else {
                    // Single Step Logic
                    $totalSteps = 1;
                    $completedCount = in_array($training->id, $completedModuleIds) ? 1 : 0;
                }

                $percent = $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0;

                return [
                    'id'        => $training->id,
                    'name'      => $training->name,
                    'completed' => $completedCount,
                    'total'     => $totalSteps,
                    'percent'   => $percent,
                    'status'    => $percent == 100 ? 'Completed' : ($percent > 0 ? 'In Progress' : 'Enrolled'),
                    'color'     => $percent == 100 ? 'success' : ($percent > 0 ? 'warning' : 'info')
                ];
            });

            return $user;
        });


        // return $trainees;
    return view('user_trainings.index', compact('trainees'));
    }

    // Show specific user's training checklist
    // public function show(User $user)
    // {
    //     $programs = TrainingModule::whereNull('parent_id')->with('steps')->get();
    //     $completedIds = $user->trainings()->pluck('training_module_id')->toArray();

    //     return view('user_trainings.show', compact('user', 'programs', 'completedIds'));
    // }

    // // Log the interaction
    public function store(Request $request, User $user,TrainingModule $training)
    {
        if (auth()->user()?->hasRole('Trainee') && auth()->id() !== $user->id) {
            abort(403, 'You are not allowed to update another trainee\'s progress.');
        }

        // $user->trainings()->syncWithoutDetaching([
        //     $request->module_id => [
        //         'interacted_person' => $request->interacted_person,
        //         'designation'       => $request->designation,
        //         'comments'          => $request->comments,
        //         'is_completed'      => true,
        //         'completed_at'      => now(),
        //     ]
        // ]);

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
