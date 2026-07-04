<?php

namespace App\Http\Controllers;

use App\Models\DocumentReadTracker;
use App\Models\ExamResult;
use App\Models\TrainingModule;
use App\Models\TrainingSessions;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $isAdmin    = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'super-admin']);
        $isReviewer = !$isAdmin && $user->hasRole('Reviewer');
        $isApprover = !$isAdmin && $user->hasRole('Approver');
        $isTrainer  = (int) $user->is_trainer === 1;
        $isTrainee  = $user->hasRole('Trainee');

        $data = match (true) {
            $isAdmin    => $this->adminData(),
            $isTrainer  => $this->trainerData($user),
            $isReviewer => $this->reviewerData(),
            $isApprover => $this->approverData(),
            default     => $this->traineeData($user),
        };

        return view('home', array_merge($data, compact(
            'isAdmin', 'isTrainer', 'isReviewer', 'isApprover', 'isTrainee'
        )));
    }

    // ─────────────────────────────────────────────────────────────
    // ADMIN
    // ─────────────────────────────────────────────────────────────
    private function adminData(): array
    {
        $totalUsers    = User::where('is_trainer', 0)->count();
        $totalTrainers = User::where('is_trainer', 1)->count();
        $activeTrainings  = TrainingModule::whereNull('parent_id')->where('is_active', 1)->count();
        $setupTrainings   = TrainingModule::whereNull('parent_id')->where('is_active', 0)->count();

        // Trainings in 'inreview' — waiting for reviewer
        $inreviewTrainings = TrainingModule::where('status', 'inreview')
            ->whereNull('parent_id')
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'status', 'training_type', 'created_at']);

        // Trainings in 'reviewed' — waiting for approver
        $reviewedTrainings = TrainingModule::where('status', 'reviewed')
            ->whereNull('parent_id')
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'status', 'training_type', 'created_at']);

        // Trainer acceptances still pending on active trainings
        $pendingAcceptanceModules = TrainingModule::where('is_active', 1)
            ->whereHas('trainers', fn($q) => $q->where('trainer_training.acceptance_status', 'pending'))
            ->with(['trainers' => fn($q) => $q->wherePivot('acceptance_status', 'pending')])
            ->take(5)
            ->get(['id', 'name']);

        // Training sessions not yet approved
        $pendingSessions = TrainingSessions::where('is_approved', false)
            ->with(['trainee:id,name', 'trainer:id,name'])
            ->latest('training_date')
            ->take(5)
            ->get();

        // Exam pass/fail totals
        $examStats = [
            'total'  => ExamResult::count(),
            'passed' => ExamResult::where('is_passed', true)->count(),
            'failed' => ExamResult::where('is_passed', false)->count(),
        ];

        // Recently activated trainings
        $recentTrainings = TrainingModule::whereNull('parent_id')
            ->where('is_active', 1)
            ->withCount('trainees')
            ->latest('activated_at')
            ->take(6)
            ->get(['id', 'name', 'status', 'training_type', 'start_date', 'end_date', 'activated_at']);

        return compact(
            'totalUsers', 'totalTrainers', 'activeTrainings', 'setupTrainings',
            'inreviewTrainings', 'reviewedTrainings', 'pendingAcceptanceModules',
            'pendingSessions', 'examStats', 'recentTrainings'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // TRAINER
    // ─────────────────────────────────────────────────────────────
    private function trainerData(User $user): array
    {
        $acceptedTrainings = $user->modules()
            ->wherePivot('acceptance_status', 'accepted')
            ->where('training_modules.is_active', 1)
            ->withCount('trainees')
            ->get();

        $pendingAcceptances = $user->modules()
            ->wherePivot('acceptance_status', 'pending')
            ->get(['training_modules.id', 'training_modules.name', 'training_modules.start_date', 'training_modules.end_date', 'training_modules.training_type']);

        $totalTrainees   = $acceptedTrainings->sum('trainees_count');
        $acceptedCount   = $acceptedTrainings->count();
        $pendingCount    = $pendingAcceptances->count();

        $upcomingTrainings = $user->modules()
            ->wherePivot('acceptance_status', 'accepted')
            ->where('training_modules.is_active', 1)
            ->whereDate('training_modules.start_date', '>=', now()->toDateString())
            ->orderBy('training_modules.start_date')
            ->take(5)
            ->get(['training_modules.id', 'training_modules.name', 'training_modules.start_date', 'training_modules.end_date', 'training_modules.training_type']);

        return compact(
            'acceptedTrainings', 'pendingAcceptances',
            'totalTrainees', 'acceptedCount', 'pendingCount', 'upcomingTrainings'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // REVIEWER
    // ─────────────────────────────────────────────────────────────
    private function reviewerData(): array
    {
        $pendingReview = TrainingModule::where('status', 'inreview')
            ->whereNull('parent_id')
            ->with('creator:id,name')
            ->latest()
            ->get(['id', 'name', 'status', 'training_type', 'created_at', 'created_by']);

        $reviewedCount = TrainingModule::where('status', 'reviewed')->whereNull('parent_id')->count();
        $approvedCount = TrainingModule::where('status', 'approved')->whereNull('parent_id')->count();
        $pendingCount  = $pendingReview->count();

        return compact('pendingReview', 'reviewedCount', 'approvedCount', 'pendingCount');
    }

    // ─────────────────────────────────────────────────────────────
    // APPROVER
    // ─────────────────────────────────────────────────────────────
    private function approverData(): array
    {
        $pendingApproval = TrainingModule::where('status', 'reviewed')
            ->whereNull('parent_id')
            ->with('creator:id,name')
            ->latest()
            ->get(['id', 'name', 'status', 'training_type', 'created_at', 'created_by']);

        $approvedCount = TrainingModule::where('status', 'approved')->whereNull('parent_id')->count();
        $pendingCount  = $pendingApproval->count();

        return compact('pendingApproval', 'approvedCount', 'pendingCount');
    }

    // ─────────────────────────────────────────────────────────────
    // TRAINEE / EMPLOYEE
    // ─────────────────────────────────────────────────────────────
    private function traineeData(User $user): array
    {
        $enrolledTrainings = $user->trainings()
            ->where('training_modules.is_active', 1)
            ->with('documents')
            ->get();

        $totalEnrolled = $enrolledTrainings->count();
        $passedCount   = ExamResult::where('user_id', $user->id)->where('is_passed', true)->count();
        $failedCount   = ExamResult::where('user_id', $user->id)->where('is_passed', false)->count();

        // For each enrolled training, determine what the user should do next
        $actionItems = $enrolledTrainings->map(function ($training) use ($user) {
            $tracker = DocumentReadTracker::where('user_id', $user->id)
                ->where('training_module_id', $training->id)
                ->first();

            $latestResult = ExamResult::where('user_id', $user->id)
                ->where('training_module_id', $training->id)
                ->latest()
                ->first();

            $hasDocuments    = $training->documents->count() > 0;
            $readingDone     = $tracker && $tracker->completed_at;
            $examPassed      = $latestResult && $latestResult->is_passed;
            $examAttempted   = (bool) $latestResult;

            $nextStep = match (true) {
                $examPassed              => 'completed',
                !$hasDocuments           => 'no_documents',
                !$readingDone            => 'read_documents',
                !$examAttempted          => 'take_exam',
                default                  => 'retake_exam',
            };

            return [
                'training'       => $training,
                'next_step'      => $nextStep,
                'reading_done'   => $readingDone,
                'exam_passed'    => $examPassed,
                'latest_result'  => $latestResult,
            ];
        });

        $pendingItems    = $actionItems->whereNotIn('next_step', ['completed', 'no_documents'])->values();
        $completedItems  = $actionItems->where('next_step', 'completed')->values();

        $recentResults = ExamResult::where('user_id', $user->id)
            ->with('module:id,name')
            ->latest()
            ->take(5)
            ->get();

        return compact(
            'totalEnrolled', 'passedCount', 'failedCount',
            'pendingItems', 'completedItems', 'recentResults'
        );
    }
}
