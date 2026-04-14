<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TrainingSessions;
use Illuminate\Http\Request;

class TrainingSessionController extends Controller
{

    public function index(Request $request)
    {
        // 1. Fetch all training sessions with Trainee and Trainer relationships
        // We use latest() to show the most recent entries at the top of the log book
        $sessions = TrainingSessions::with(['trainee.department', 'trainer.designation'])
            ->latest('training_date')
            ->paginate(15); // Use pagination for large log books

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
            'trainer_id'    => 'required|exists:users,id',
            'topic'         => 'required|string',
            'register_no'   => 'required',
            'page_no'       => 'required',
        ]);

        TrainingSessions::create($request->all());

        return back()->with('success', 'Training Register updated successfully.');
    }


    public function userReport(User $user)
    {
        // Fetch all sessions where this user is the trainee
        $sessions = TrainingSessions::where('trainee_id', $user->id)
            ->with('trainer')
            ->orderBy('training_date', 'asc')
            ->get();

        return view('training_sessions.user_report', compact('user', 'sessions'));
    }

    public function approve($id)
    {
        $session = TrainingSessions::findOrFail($id);

        // Update the session with the signer's ID and timestamp
        $session->update([
            'is_approved' => true,
            'approved_by' => auth()->id(), // The person clicking the button
            'approved_at' => now()
        ]);

        return back()->with('success', 'Digital signature applied successfully.');
    }


}
