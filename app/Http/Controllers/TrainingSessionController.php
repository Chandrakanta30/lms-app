<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TrainingModule;
use App\Models\TrainingSessions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TrainingSessionController extends Controller
{
    public function index(Request $request)
    {
        $sessionsQuery = TrainingSessions::query()
            ->with(['trainee.department', 'trainer.designation', 'approver', 'module'])
            ->whereNotNull('training_sessions.training_module_id')
            ->join('exam_results', function ($join) {
                $join->on('exam_results.user_id', '=', 'training_sessions.trainee_id')
                    ->on('exam_results.training_module_id', '=', 'training_sessions.training_module_id')
                    ->where('exam_results.is_passed', true);
            });

        if ($request->filled('trainee_id')) {
            $sessionsQuery->where('training_sessions.trainee_id', $request->trainee_id);
        }

        if ($request->filled('training_module_id')) {
            $sessionsQuery->where('training_sessions.training_module_id', $request->training_module_id);
        }

        if ($request->filled('topic')) {
            $sessionsQuery->where('training_sessions.topic', 'like', '%' . $request->topic . '%');
        }

        if ($request->filled('date_from')) {
            $sessionsQuery->whereDate('training_sessions.training_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $sessionsQuery->whereDate('training_sessions.training_date', '<=', $request->date_to);
        }

        $firstSessionIds = (clone $sessionsQuery)
            ->selectRaw('MIN(training_sessions.id) as id')
            ->groupBy('training_sessions.trainee_id', 'training_sessions.training_module_id');

        $sessions = TrainingSessions::with(['trainee.department', 'trainer.designation', 'approver', 'module'])
            ->joinSub($firstSessionIds, 'first_sessions', function ($join) {
                $join->on('training_sessions.id', '=', 'first_sessions.id');
            })
            ->select('training_sessions.*')
            ->orderBy('training_sessions.training_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 2. Fetch authorized Trainers for the 'Add New' modal dropdown
        $trainers = User::where('is_trainer', true)
            ->with('designation')
            ->get();

        $modules = TrainingModule::orderBy('name')->get();

        // 3. Fetch only passed/eligible trainees for the 'Add New' modal dropdown
        $trainees = User::whereHas('examResults', function ($examQuery) {
                $examQuery->where('is_passed', true);
            })
            ->with('department')
            ->get();

        return view('training_sessions.index', compact('sessions', 'trainers', 'trainees', 'modules'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'training_date' => 'required|date',
            'trainee_id' => 'required|exists:users,id',
            'training_module_id' => 'required|exists:training_modules,id',
            'trainer_id' => 'nullable|exists:users,id',
            'topic' => 'required|string',
            'register_no' => 'required',
            'page_no' => 'required',
        ]);

        $payload = $request->only([
            'training_date',
            'trainee_id',
            'training_module_id',
            'trainer_id',
            'register_no',
            'page_no',
            'topic',
        ]);
        $payload['trainer_id'] = $request->trainer_id ?: auth()->id();

        TrainingSessions::updateOrCreate(
            [
                'trainee_id' => $payload['trainee_id'],
                'training_module_id' => $payload['training_module_id'],
            ],
            $payload
        );
        $user = User::find($request->trainee_id);
        $traineeRole = Role::findOrCreate('Trainee', 'web');
        $user->assignRole($traineeRole);


        return back()->with('success', 'Training Register updated successfully.');
    }


    public function userReport(User $user)
    {
        $sessionsQuery = TrainingSessions::query()
            ->with(['trainer', 'approver', 'module'])
            ->where('training_sessions.trainee_id', $user->id)
            ->whereNotNull('training_sessions.training_module_id')
            ->join('exam_results', function ($join) {
                $join->on('exam_results.user_id', '=', 'training_sessions.trainee_id')
                    ->on('exam_results.training_module_id', '=', 'training_sessions.training_module_id')
                    ->where('exam_results.is_passed', true);
            });

        $firstSessionIds = (clone $sessionsQuery)
            ->selectRaw('MIN(training_sessions.id) as id')
            ->groupBy('training_sessions.trainee_id', 'training_sessions.training_module_id');

        $sessions = TrainingSessions::with(['trainer', 'approver', 'module'])
            ->joinSub($firstSessionIds, 'first_sessions', function ($join) {
                $join->on('training_sessions.id', '=', 'first_sessions.id');
            })
            ->select('training_sessions.*')
            ->orderBy('training_sessions.training_date', 'asc')
            ->get();

        return view('training_sessions.user_report', compact('user', 'sessions'));
    }

    // public function approve($id)
    // {
    //     $session = TrainingSessions::findOrFail($id);

    //     // Update the session with the signer's ID and timestamp
    //     $session->update([
    //         'is_approved' => true,
    //         'approved_by' => auth()->id(), // The person clicking the button
    //         'approved_at' => now()
    //     ]);

    //     return back()->with('success', 'Digital signature applied successfully.');
    // }
    public function approve($id)
    {
        $session = TrainingSessions::findOrFail($id);

        if ($session->is_approved) {
            return back()->with('info', 'This session is already approved.');
        }


        $session->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Get trainee user
        $user = $session->trainee;

        if ($user) {

            if ($user->hasRole('trainee')) {
                $user->removeRole('trainee');
            }

            $user->assignRole('regular');
        }

        return back()->with('success', 'Session approved successfully and trainee promoted to regular.');
    }
}
