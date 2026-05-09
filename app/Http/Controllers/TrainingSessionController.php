<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TrainingSessions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TrainingSessionController extends Controller
{

    public function index(Request $request)
    {
        $sessionsQuery = TrainingSessions::with(['trainee.department', 'trainer.designation', 'approver'])
            ->latest('training_date');

        if ($request->filled('trainee_id')) {
            $sessionsQuery->where('trainee_id', $request->trainee_id);
        }

        if ($request->filled('topic')) {
            $sessionsQuery->where('topic', 'like', '%' . $request->topic . '%');
        }

        if ($request->filled('date_from')) {
            $sessionsQuery->whereDate('training_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $sessionsQuery->whereDate('training_date', '<=', $request->date_to);
        }

        $sessions = $sessionsQuery->paginate(15)->withQueryString();

        // 2. Fetch authorized Trainers for the 'Add New' modal dropdown
        $trainers = User::where('is_trainer', true)
            ->with('designation')
            ->get();

        // 3. Fetch all Trainees for the 'Add New' modal dropdown
        // Note: You can filter by role('trainee') if using Spatie
        $trainees = User::role('Trainee')
            ->with('department')
            ->get();

        return view('training_sessions.index', compact('sessions', 'trainers', 'trainees'));
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'training_date' => 'required|date',
            'trainee_id'    => 'required|exists:users,id',
            'trainer_id'    => 'nullable|exists:users,id',
            'topic'         => 'required|string',
            'register_no'   => 'required',
            'page_no'       => 'required',
        ]);

        $payload = $request->all();
        $payload['trainer_id'] = $request->trainer_id ?: auth()->id();

        TrainingSessions::create($payload);
        $user = User::find($request->trainee_id);
        $traineeRole = Role::findOrCreate('Trainee', 'web');
        $user->assignRole($traineeRole);


        return back()->with('success', 'Training Register updated successfully.');
    }


    public function userReport(User $user)
    {
        // Fetch all sessions where this user is the trainee
        $sessions = TrainingSessions::where('trainee_id', $user->id)
            ->with(['trainer', 'approver'])
            ->orderBy('training_date', 'asc')
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
        'is_approved'  => true,
        'approved_by'  => Auth::id(),
        'approved_at'  => now(),
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
