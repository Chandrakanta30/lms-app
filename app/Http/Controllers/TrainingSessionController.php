<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\ExamResult;
use App\Models\User;
use App\Models\TrainingSessions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TrainingSessionController extends Controller
{
    public function index(Request $request)
    {
        $sessionsQuery = TrainingSessions::query()
            ->with(['trainee.department', 'trainer.designation', 'approver'])
            ->whereHas('trainee', function ($query) {
                $query->whereHas('examResults', function ($examQuery) {
                    $examQuery->where('is_passed', true);
                });
            });

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

        $firstSessionIds = (clone $sessionsQuery)
            ->selectRaw('MIN(training_sessions.id) as id')
            ->groupBy('training_sessions.trainee_id');

        $sessions = TrainingSessions::with(['trainee.department', 'trainer.designation', 'approver'])
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

        // 3. Fetch only passed/eligible trainees for the 'Add New' modal dropdown
        $trainees = User::whereHas('examResults', function ($examQuery) {
                $examQuery->where('is_passed', true);
            })
            ->with('department')
            ->get();

        return view('training_sessions.index', compact('sessions', 'trainers', 'trainees'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'training_date' => 'required|date',
            'trainee_id' => 'required|exists:users,id',
            'trainer_id' => 'nullable|exists:users,id',
            'topic' => 'required|string',
            'register_no' => 'required',
            'page_no' => 'required',
        ]);

        $payload = $request->only([
            'training_date',
            'trainee_id',
            'trainer_id',
            'register_no',
            'page_no',
            'topic',
        ]);
        $payload['trainer_id'] = $request->trainer_id ?: auth()->id();

        TrainingSessions::updateOrCreate(
            [
                'trainee_id' => $payload['trainee_id'],
                'topic' => $payload['topic'],
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
        $sessions = TrainingSessions::query()
            ->with(['trainer', 'approver'])
            ->where('training_sessions.trainee_id', $user->id)
            ->orderBy('training_sessions.training_date', 'asc')
            ->get()
            ->filter(function (TrainingSessions $session) {
                return $this->sessionHasPassedTraining($session);
            })
            ->unique('topic')
            ->values();

        return view('training_sessions.user_report', compact('user', 'sessions'));
    }

    private function sessionHasPassedTraining(TrainingSessions $session): bool
    {
        $topic = trim((string) $session->topic);

        if ($topic === '') {
            return false;
        }

        $candidateLabels = [$topic];
        $topicPrefix = trim(explode(' - ', $topic, 2)[0]);

        if ($topicPrefix !== '' && $topicPrefix !== $topic) {
            $candidateLabels[] = $topicPrefix;
        }

        $passedModuleNames = ExamResult::where('user_id', $session->trainee_id)
            ->where('is_passed', true)
            ->with('module:id,name')
            ->get()
            ->pluck('module.name')
            ->filter()
            ->map(fn ($name) => $this->normalizeTrainingLabel($name))
            ->unique()
            ->values();

        foreach ($candidateLabels as $candidateLabel) {
            $normalizedCandidate = $this->normalizeTrainingLabel($candidateLabel);

            foreach ($passedModuleNames as $passedModuleName) {
                if (
                    $normalizedCandidate === $passedModuleName
                    || str_contains($passedModuleName, $normalizedCandidate)
                    || str_contains($normalizedCandidate, $passedModuleName)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalizeTrainingLabel(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim(mb_strtolower($value)));
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
