<?php

namespace App\Http\Controllers;

use App\Models\TrainingModule;
use Illuminate\Support\Facades\Auth;
use App\Models\ExamResult;
use App\Models\User;
use App\Models\TrainingUser;
use App\Models\TrainingSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class TrainingSessionController extends Controller
{
    public function index(Request $request)
    {
        $assignmentsQuery = TrainingUser::query()
            ->with([
                'user.department',
                'user.designation',
                'module.trainers',
            ]);

        if (auth()->user()?->hasRole('Trainee')) {
            $assignmentsQuery->where('user_id', auth()->id());
        }

        if ($request->filled('trainee_id')) {
            $assignmentsQuery->where('user_id', $request->trainee_id);
        }

        if ($request->filled('topic')) {
            $topic = trim((string) $request->topic);

            $assignmentsQuery->whereHas('module', function ($query) use ($topic) {
                $query->where('name', 'like', '%' . $topic . '%');
            });
        }

        if ($request->filled('date_from')) {
            $assignmentsQuery->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $assignmentsQuery->whereDate('end_date', '<=', $request->date_to);
        }

        $assignments = $assignmentsQuery
            ->orderByDesc('training_user.id')
            ->paginate(15)
            ->withQueryString();

        $statusCache = [];
        $assignments->getCollection()->transform(function (TrainingUser $assignment) use (&$statusCache) {
            $cacheKey = $assignment->user_id . '|' . $assignment->training_module_id;
            $module = $assignment->module;
            $user = $assignment->user;

            if (!array_key_exists($cacheKey, $statusCache)) {
                $statusCache[$cacheKey] = $module && $user
                    ? $module->syncTrainingStatusForUser($user)
                    : ($assignment->status ?? 'pending');
            }

            $assignment->status = $statusCache[$cacheKey];
            $assignment->status_label = $this->formatTrainingStatusLabel($assignment->status);
            $assignment->status_class = $this->formatTrainingStatusClass($assignment->status);
            $assignment->can_sign_and_approve = $assignment->status === 'passed';
            $assignment->latest_exam_result = $module && $user
                ? $module->examResults()
                    ->where('user_id', $user->id)
                    ->latest('created_at')
                    ->first()
                : null;
            $assignment->reassignment_note = $assignment->reassignment_note
                ?: $this->buildReassignmentNote($assignment);
            $assignment->can_reassign = $assignment->status === 'failed'
                && $module
                && (
                    !$assignment->reassigned_at
                    || (
                        $assignment->latest_exam_result
                        && $assignment->latest_exam_result->created_at
                        && Carbon::parse($assignment->latest_exam_result->created_at)->gt(Carbon::parse($assignment->reassigned_at))
                    )
                );
            $assignment->trainer_name = optional($module?->trainers?->first())->name ?? 'N/A';
            $assignment->signature_session = $module && $user
                ? $this->resolveTrainingSessionForAssignment($assignment)
                : null;
            return $assignment;
        });

        $reassignmentTrainings = $this->reassignmentTrainingOptions();

        $trainers = User::where('is_trainer', true)
            ->with('designation')
            ->get();

       
        $trainees = User::query()
            ->with('department')
            ->get();

        return view('training_sessions.index', compact('assignments', 'trainers', 'trainees', 'reassignmentTrainings'));
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
            $payload['trainer_id'] = $request->trainer_id ?: null;

            TrainingSessions::updateOrCreate(
                [
                    'trainee_id' => $payload['trainee_id'],
                    'topic' => $payload['topic'],
                ],
                $payload
            );
            $user = User::find($request->trainee_id);
            $module = $this->resolveTrainingModuleForTopic($payload['topic']);

            if ($module && $user) {
                TrainingUser::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'training_module_id' => $module->id,
                    ],
                    [
                        'start_date' => $module->start_date ?? $request->training_date,
                        'end_date' => $module->end_date ?? $request->training_date,
                        'status' => $module->syncTrainingStatusForUser($user),
                    ]
                );
            }

            $this->syncTrainingStatusByTopic($payload['topic'], $user);
            $traineeRole = Role::findOrCreate('Trainee', 'web');
            $user->assignRole($traineeRole);


            return back()->with('success', 'Training Register updated successfully.');
        }


    public function userReport(User $user)
    {
        $sessions = TrainingUser::query()
            ->with(['module.trainers', 'user.department', 'user.designation'])
            ->where('user_id', $user->id)
            ->orderBy('training_user.id', 'asc')
            ->get()
            ->map(function (TrainingUser $assignment) {
                $assignment->status = $assignment->module && $assignment->user
                    ? $assignment->module->syncTrainingStatusForUser($assignment->user)
                    : ($assignment->status ?? 'pending');
                $assignment->status_label = $this->formatTrainingStatusLabel($assignment->status);
                $assignment->status_class = $this->formatTrainingStatusClass($assignment->status);
                $assignment->trainer_name = optional($assignment->module?->trainers?->first())->name ?? 'N/A';
                $assignment->signature_session = $this->resolveTrainingSessionForAssignment($assignment);
                $assignment->latest_exam_result = $assignment->module
                    ? $assignment->module->examResults()
                        ->where('user_id', $assignment->user_id)
                        ->latest('created_at')
                        ->first()
                    : null;
                $assignment->reassignment_note = $assignment->reassignment_note
                    ?: $this->buildReassignmentNote($assignment);
                $assignment->can_reassign = $assignment->status === 'failed'
                    && $assignment->module
                    && (
                        !$assignment->reassigned_at
                        || (
                            $assignment->latest_exam_result
                            && $assignment->latest_exam_result->created_at
                            && Carbon::parse($assignment->latest_exam_result->created_at)->gt(Carbon::parse($assignment->reassigned_at))
                        )
                    );

                return $assignment;
            });

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

        if (!$this->isSessionEligibleForApproval($session)) {
            return back()->with('error', 'Sign & Approve is disabled until the trainee passes the exam.');
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

    public function reassign(Request $request, TrainingUser $assignment)
    {
        $currentUser = auth()->user();
        $session = $this->resolveTrainingSessionForAssignment($assignment);

        if (
            !$currentUser ||
            !(
                $currentUser->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'super-admin']) ||
                ($session && $currentUser->id === $session->trainer_id)
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $module = $assignment->module;
        $user = $assignment->user;

        if (!$module || !$user) {
            return back()->with('error', 'Unable to re-assign this training because the linked records are missing.');
        }

        $request->validate([
            'reassignment_target' => 'required|integer|exists:training_modules,id',
            'reassignment_scope' => 'required|in:same,other',
        ]);

        $targetTrainingId = (int) $request->input('reassignment_target');
        $targetTraining = TrainingModule::findOrFail($targetTrainingId);
        $scope = $request->input('reassignment_scope');

        if ($scope === 'same' && $targetTraining->id !== $module->id) {
            return back()->with('error', 'Please choose the same failed training for a same-training reassignment.');
        }

        if ($scope === 'same' && $targetTraining->isExpired()) {
            return back()->with('error', 'This training has expired, so you must choose another active training instead.');
        }

        if ($scope === 'other' && $targetTraining->id === $module->id) {
            return back()->with('error', 'Please choose another active training for this reassignment.');
        }

        if ($scope === 'other' && $targetTraining->isExpired()) {
            return back()->with('error', 'The selected training is expired and cannot be used for reassignment.');
        }

        $reassignmentNote = $this->formatReassignmentNote($targetTraining);
        $currentAssignmentUpdatedAt = now();

        DB::transaction(function () use ($assignment, $user, $module, $targetTraining, $scope, $reassignmentNote, $currentAssignmentUpdatedAt) {
            if ($scope === 'same') {
                $assignment->update([
                    'status' => 'pending',
                    'reassigned_at' => $currentAssignmentUpdatedAt,
                    'reassignment_mode' => $scope,
                    'reassignment_note' => $reassignmentNote,
                    'reassigned_from_training_id' => $module->id,
                ]);

                return;
            }

            $assignment->update([
                'reassigned_at' => $currentAssignmentUpdatedAt,
                'reassignment_mode' => $scope,
                'reassignment_note' => $reassignmentNote,
                'reassigned_from_training_id' => $module->id,
            ]);

            TrainingUser::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'training_module_id' => $targetTraining->id,
                ],
                [
                    'status' => 'pending',
                    'start_date' => $targetTraining->start_date ?? $module->start_date ?? now()->toDateString(),
                    'end_date' => $targetTraining->end_date ?? $module->end_date ?? now()->toDateString(),
                    'reassigned_at' => $currentAssignmentUpdatedAt,
                    'reassignment_mode' => 'other',
                    'reassignment_note' => $reassignmentNote,
                    'reassigned_from_training_id' => $module->id,
                ]
            );
        });

        activity()
            ->performedOn($assignment)
            ->causedBy($currentUser)
            ->withProperties([
                'training_user_id' => $assignment->id,
                'trainee_id' => $user->id,
                'from_training_id' => $module->id,
                'from_training_name' => $module->name,
                'to_training_id' => $targetTraining->id,
                'to_training_name' => $targetTraining->name,
                'scope' => $scope,
                'reassignment_note' => $reassignmentNote,
            ])
            ->log('training reassigned');

        return back()->with('success', 'Training re-assigned successfully.');    
    }

    private function syncTrainingStatusByTopic(string $topic, ?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        $module = $this->resolveTrainingModuleForTopic($topic);

        if (!$module) {
            return null;
        }

        return $module->syncTrainingStatusForUser($user);
    }

    private function isSessionEligibleForApproval(TrainingSessions $session): bool
    {
        if ($session->is_approved) {
            return true;
        }

        $module = $this->resolveTrainingModuleForTopic((string) $session->topic);

        if (!$module || !$session->trainee) {
            return false;
        }

        return $module->resolveTrainingStatusForUser($session->trainee) === 'passed';
    }

    private function resolveTrainingModuleForTopic(string $topic): ?TrainingModule
    {
        $normalizedTopic = $this->normalizeTrainingLabel($topic);

        if ($normalizedTopic === '') {
            return null;
        }

        $candidateLabels = [$normalizedTopic];
        $topicPrefix = trim(explode(' - ', $topic, 2)[0]);

        if ($topicPrefix !== '' && $topicPrefix !== $topic) {
            $candidateLabels[] = $this->normalizeTrainingLabel($topicPrefix);
        }

        static $modules = null;

        $modules ??= TrainingModule::query()
            ->with('steps:id,parent_id,name')
            ->get(['id', 'parent_id', 'name']);

        foreach ($candidateLabels as $candidateLabel) {
            foreach ($modules as $module) {
                $moduleNames = collect([$module->name])
                    ->merge($module->steps->pluck('name'))
                    ->map(fn (string $name) => $this->normalizeTrainingLabel($name));

                if (
                    $moduleNames->contains($candidateLabel)
                    || $moduleNames->contains(fn (string $name) => str_contains($name, $candidateLabel))
                    || str_contains($candidateLabel, $this->normalizeTrainingLabel($module->name))
                ) {
                    return $module;
                }
            }
        }

        return null;
    }

    private function formatTrainingStatusLabel(?string $status): string
    {
        return match ($status) {
            'passed' => 'Passed',
            'failed' => 'Failed',
            default => 'Pending',
        };
    }

    private function formatTrainingStatusClass(?string $status): string
    {
        return match ($status) {
            'passed' => 'badge-success',
            'failed' => 'badge-danger',
            default => 'badge-warning',
        };
    }

    private function formatReassignmentNote(TrainingModule $training): string
    {
        $dateText = $training->start_date
            ? Carbon::parse($training->start_date)->format('d M Y')
            : now()->format('d M Y');

        return 'reassign -> ' . $training->name . ' | ' . $dateText;
    }

    private function buildReassignmentNote(TrainingUser $assignment): ?string
    {
        $trainingName = $assignment->module?->name ?? null;

        if (!$trainingName) {
            return null;
        }

        $dateSource = $assignment->module?->start_date
            ?? $assignment->reassigned_at
            ?? now();

        return 'reassign -> ' . $trainingName . ' | ' . Carbon::parse($dateSource)->format('d M Y');
    }

    private function reassignmentTrainingOptions()
    {
        return TrainingModule::query()
            ->where('is_active', 1)
            ->where(function ($query) {
                $query->where(function ($regularQuery) {
                    $regularQuery->whereNull('is_anuual')
                        ->orWhere('is_anuual', '0');
                })->orWhere(function ($annualQuery) {
                    $annualQuery->where('is_anuual', '1')
                        ->whereNotNull('annual_parent_id');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'start_date', 'end_date', 'is_anuual', 'annual_parent_id', 'is_active']);
    }

    private function resolveTrainingSessionForAssignment(TrainingUser $assignment): ?TrainingSessions
    {
        $moduleName = trim((string) optional($assignment->module)->name);

        if ($moduleName === '') {
            return null;
        }

        $topicPrefix = trim(explode(' - ', $moduleName, 2)[0]);
        $candidateLabels = array_values(array_filter([
            $moduleName,
            $topicPrefix !== '' ? $topicPrefix : null,
        ]));

        return TrainingSessions::query()
            ->where('trainee_id', $assignment->user_id)
            ->where(function ($query) use ($candidateLabels) {
                foreach ($candidateLabels as $label) {
                    $query->orWhere('topic', 'like', '%' . $label . '%');
                }
            })
            ->latest('training_date')
            ->latest('id')
            ->first()
            ?? TrainingSessions::query()
                ->where('trainee_id', $assignment->user_id)
                ->latest('training_date')
                ->latest('id')
                ->first();
    }
}
