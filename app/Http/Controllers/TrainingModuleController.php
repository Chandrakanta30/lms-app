<?php

namespace App\Http\Controllers;


use App\Models\Department;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\Auth;

use App\Models\TrainingDocument;
use App\Models\TrainingModule;
use App\Models\TrainingSessions;
use App\Models\User;
use App\Models\Venue;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class TrainingModuleController extends Controller
{
    public function index()
    {
        $routeName = request()->route()->getName();

        $query = TrainingModule::with([
            'steps',
            'trainers.designation',
            'trainees.designation',
            'documents'
        ])->whereNull('parent_id');

        if ($routeName === 'trainings.index') {
            $query = $query->where('is_active', 0);

        } elseif ($routeName === 'created-training-setup') {
            $query = $query->where('is_active', 1);

        } elseif ($routeName === 'annual-training') {
            $query = $query->whereNotNull('annual_parent_id')
                ->where('is_anuual', '1')
                ->where('is_active', 0);

        } elseif ($routeName === 'created-annual-training') {
            $query = $query->whereNotNull('annual_parent_id')
                ->where('is_anuual', '1')
                ->where('is_active', 1);
        }

        $trainings = $query->latest('id')->get();

        $statusOptions = TrainingModule::STATUSES;

        return view('trainings.index', compact('trainings', 'statusOptions'));
    }

    public function create()
    {
        $statusOptions = TrainingModule::STATUSES;
        $departments = Department::all();
        $subdepartments = SubDepartment::all();

        return view('trainings.create', compact('statusOptions', 'departments', 'subdepartments'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'training_type' => 'required|in:classroom,self_training',
    //         'status' => 'required|in:' . implode(',', TrainingModule::STATUSES),
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'start_time' => 'nullable',
    //         'end_time' => 'nullable',

    //         'step_names' => 'nullable|array',
    //         'step_names.*' => 'nullable|string|max:255',
    //         'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
    //         'docs.*.name' => 'required_if:training_type,self_training',
    //         'docs.*.file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',

    //     ]);

    //     $parent = TrainingModule::create([
    //         'name' => $request->name,
    //         'training_type' => $request->training_type,
    //         'status' => $request->status,
    //         'start_date' => $request->start_date,
    //         'end_date' => $request->end_date,
    //         'start_time' => $request->start_time,
    //         'end_time' => $request->end_time,
    //         'parent_id' => null,
    //         'created_by' => auth()->id(),
    //         'updated_by' => auth()->id(),
    //         'is_active' => true,
    //         'activated_at' => now(),
    //         'activated_by' => auth()->id(),
    //     ]);

    //     foreach (array_values(array_filter($request->input('step_names', []), fn($stepName) => filled($stepName))) as $index => $stepName) {
    //         $parent->steps()->create([
    //             'name' => $stepName,
    //             'step_number' => $index + 1,
    //             'training_type' => $request->training_type,
    //             'status' => $request->status,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'start_time' => $request->start_time,
    //             'end_time' => $request->end_time,
    //         ]);
    //     }

    //     if ($request->training_type === 'self_training' && $request->has('docs')) {
    //         foreach ($request->docs as $doc) {
    //             if (isset($doc['file']) && $doc['file'] instanceof UploadedFile) {
    //                 $path = $doc['file']->store('training_materials', 'public');

    //                 TrainingDocument::create([
    //                     'training_id' => $parent->id,
    //                     'doc_type' => $doc['type'],
    //                     'doc_name' => $doc['name'],
    //                     'doc_number' => $doc['number'] ?? 'N/A',
    //                     'doc_version' => $doc['version'] ?? 'v1.0',
    //                     'file_path' => $path,
    //                 ]);
    //             }
    //         }
    //     }

    //     activity()
    //         ->performedOn($parent)
    //         ->causedBy(auth()->user())
    //         ->withProperties([
    //             'attributes' => [
    //                 'name' => $parent->name,
    //                 'status' => $parent->status,
    //                 'training_type' => $parent->training_type,
    //             ],
    //         ])
    //         ->log('created');

    //     return redirect()->route('trainings.index')->with('success', 'Training program and materials created successfully.');
    // }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'training_type' => 'required|in:classroom,self_training',
            'status' => 'required|in:' . implode(',', TrainingModule::STATUSES),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',

            'is_annual' => 'nullable',
            'frequency' => 'nullable|in:monthly,quarterly,half_yearly,yearly',

            'department_id' => 'nullable|integer',
            'subdepartment_id' => 'nullable|integer',

            'step_names' => 'nullable|array',
            'step_names.*' => 'nullable|string|max:255',

            'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
            'docs.*.name' => 'required_if:training_type,self_training',
            'docs.*.file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
        ]);


        $parent = TrainingModule::create([
            'name' => $request->name,
            'training_type' => $request->training_type,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,

            'parent_id' => null,
            'annual_parent_id' => null,

            'is_anuual' => $request->input('is_annual') == '1' ? '1' : '0',
            'frequency' => $request->frequency,

            'department_id' => $request->department_id,
            'subdepartment_id' => $request->subdepartment_id,

            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'is_active' => false,
            'activated_at' => now(),
            'activated_by' => auth()->id(),
        ]);


        foreach (
            array_values(
                array_filter($request->input('step_names', []), fn($step) => filled($step))
            ) as $index => $stepName
        ) {
            $parent->steps()->create([
                'name' => $stepName,
                'step_number' => $index + 1,
                'training_type' => $request->training_type,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
        }


        if ($request->training_type === 'self_training' && $request->has('docs')) {

            foreach ($request->docs as $doc) {

                if (isset($doc['file']) && $doc['file'] instanceof UploadedFile) {

                    $path = $doc['file']->store('training_materials', 'public');

                    TrainingDocument::create([
                        'training_id' => $parent->id,
                        'doc_type' => $doc['type'],
                        'doc_name' => $doc['name'],
                        'doc_number' => $doc['number'] ?? 'N/A',
                        'doc_version' => $doc['version'] ?? 'v1.0',
                        'file_path' => $path,
                    ]);
                }
            }
        }

        if ($request->input('is_annual') == '1' && $request->frequency) {

            $frequencyMap = [
                'monthly' => 12,
                'quarterly' => 4,
                'half_yearly' => 2,
                'yearly' => 1,
            ];

            $count = $frequencyMap[$request->frequency] ?? 0;

            for ($i = 0; $i < $count; $i++) {

                $monthName = now()->addMonths($i)->format('F');

                $child = TrainingModule::create([
                    'name' => $parent->name . ' - ' . $monthName . ' Training',

                    'training_type' => $parent->training_type,
                    'status' => $parent->status,

                    'start_date' => $parent->start_date,
                    'end_date' => $parent->end_date,
                    'start_time' => $parent->start_time,
                    'end_time' => $parent->end_time,

                    'parent_id' => null,
                    'annual_parent_id' => $parent->id,

                    'is_anuual' => $parent->is_anuual,
                    'frequency' => $parent->frequency,

                    'department_id' => $parent->department_id,
                    'subdepartment_id' => $parent->subdepartment_id,

                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'is_active' => false,
                    'activated_at' => now(),
                    'activated_by' => auth()->id(),
                ]);
                // activity()
                //     ->performedOn($child)
                //     ->causedBy(auth()->user())
                //     ->withProperties([
                //         'parent_id' => $parent->id,
                //         'frequency' => $request->frequency,
                //         'month' => $monthName,
                //     ])
                //     ->log('annual training auto-generated');

                foreach ($parent->steps as $step) {
                    $child->steps()->create([
                        'name' => $step->name,
                        'step_number' => $step->step_number,
                        'training_type' => $step->training_type,
                        'status' => $step->status,
                        'start_date' => $step->start_date,
                        'end_date' => $step->end_date,
                        'start_time' => $step->start_time,
                        'end_time' => $step->end_time,
                    ]);
                }
            }
        }
        activity()
            ->performedOn($parent)
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => [
                    'name' => $parent->name,
                    'status' => $parent->status,
                    'training_type' => $parent->training_type,
                    'is_annual' => $parent->is_anuual,
                    'frequency' => $parent->frequency,
                ],
            ])
            ->log('created');

        return redirect()
            ->route('trainings.index')
            ->with('success', 'Training program created successfully.');
    }
    public function show(TrainingModule $training)
    {
        $training->load([
            'steps',
            'trainers.designation',
            'trainees.designation',
            'trainees.department',
            'documents',
        ]);

        return view('trainings.show', compact('training'));
    }

    public function edit(TrainingModule $training)
    {
        $training->load(['steps', 'documents']);
        $statusOptions = TrainingModule::STATUSES;
        $statusOptions = array_diff($statusOptions, ['approved', 'reviewed']);
        $departments = Department::all();
        $subdepartments = SubDepartment::all();
        $user = auth()->user();

        // Add back based on role
        if ($user->hasRole('Reviewer')) {
            $statusOptions[] = 'reviewed';
        }

        if ($user->hasRole('Approver')) {
            $statusOptions[] = 'approved';
        }

        return view('trainings.edit', compact('training', 'statusOptions', 'departments', 'subdepartments'));
    }

    public function update(Request $request, TrainingModule $training)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'training_type' => 'required|in:classroom,self_training',
            'status' => 'required|in:' . implode(',', TrainingModule::STATUSES),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_annual' => 'nullable',
            'frequency' => 'nullable|in:monthly,quarterly,half_yearly,yearly',
            'department_id' => 'nullable|integer',
            'subdepartment_id' => 'nullable|integer',
            'step_names' => 'nullable|array',
            'step_names.*' => 'nullable|string|max:255',
            'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
        ]);

        $oldData = $training->only([
            'name',
            'training_type',
            'status',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'is_anuual',
            'frequency',
            'department_id',
            'subdepartment_id',
        ]);

        $updateData = [
            'name' => $request->name,
            'training_type' => $request->training_type,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'updated_by' => auth()->id(),
        ];

        if (!$training->annual_parent_id) {
            $updateData['is_anuual'] = $request->input('is_annual') == '1' ? '1' : '0';
            $updateData['frequency'] = $request->frequency;
            $updateData['department_id'] = $request->department_id;
            $updateData['subdepartment_id'] = $request->subdepartment_id;
        }

        $training->update($updateData);

        $newData = $training->only([
            'name',
            'training_type',
            'status',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'is_anuual',
            'frequency',
            'department_id',
            'subdepartment_id',
        ]);

        activity()
            ->performedOn($training)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'attributes' => $newData,
            ])
            ->log('updated');

        $oldSteps = implode(', ', $training->steps()->pluck('name')->toArray());

        $training->steps()->delete();

        foreach (array_values(array_filter($request->input('step_names', []), fn($stepName) => filled($stepName))) as $index => $stepName) {
            $training->steps()->create([
                'name' => $stepName,
                'step_number' => $index + 1,
                'training_type' => $request->training_type,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
        }

        $newSteps = implode(', ', $training->steps()->pluck('name')->toArray());

        if ($oldSteps !== $newSteps) {
            activity()
                ->performedOn($training)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old' => ['steps' => $oldSteps],
                    'attributes' => ['steps' => $newSteps],
                ])
                ->log('steps updated');
        }

        if ($request->training_type === 'self_training' && $request->has('docs')) {

            $docNames = [];

            foreach ($request->docs as $doc) {
                if (isset($doc['file']) && $doc['file'] instanceof UploadedFile) {

                    $path = $doc['file']->store('training_materials', 'public');

                    TrainingDocument::create([
                        'training_id' => $training->id,
                        'doc_type' => $doc['type'],
                        'doc_name' => $doc['name'],
                        'doc_number' => $doc['number'] ?? 'N/A',
                        'doc_version' => $doc['version'] ?? 'v1.0',
                        'file_path' => $path,
                    ]);

                    $docNames[] = $doc['name'];
                }
            }

            if (!empty($docNames)) {
                activity()
                    ->performedOn($training)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'attributes' => [
                            'documents' => implode(', ', $docNames),
                        ],
                    ])
                    ->log('documents updated');
            }
        }

        return redirect()->route('trainings.index')->with('success', 'Training updated successfully.');
    }

    public function destroy(TrainingModule $training)
    {
        activity()
            ->performedOn($training)
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => [
                    'name' => $training->name,
                    'training_type' => $training->training_type,
                    'status' => $training->status,
                ],
            ])
            ->log('deleted');

        $training->delete();

        return redirect()->route('trainings.index')->with('success', 'Training deleted.');
    }

    public function manageTrainers($id)
    {
        $module = TrainingModule::with(['trainers', 'venues'])->findOrFail($id);

        $allUsers = User::orderBy('name', 'asc')
            ->where('is_trainer', 1)
            ->get();

        $allVenues = Venue::orderBy('name', 'asc')->get();

        return view('trainings.assign_trainers', compact('module', 'allUsers', 'allVenues'));
    }

    public function manageUsers($id)
    {
        $module = TrainingModule::with('trainees')->findOrFail($id);
        $allUsers = User::orderBy('name', 'asc')->get();

        return view('trainings.assign_users', compact('module', 'allUsers'));
    }

    public function saveTrainers(Request $request, $id)
    {
        $module = TrainingModule::findOrFail($id);

        // Get CURRENT trainer IDs BEFORE sync
        $existingTrainerIds = $module->trainers()->pluck('users.id')->toArray();

        // Get existing acceptance statuses
        $existingAcceptanceStatuses = $module->trainers()
            ->pluck('trainer_training.acceptance_status', 'users.id')
            ->toArray();

        $syncData = [];

        if ($request->has('trainers')) {

            foreach ($request->trainers as $data) {

                if (empty($data['user_id'])) {
                    continue;
                }

                $userId = $data['user_id'];

                $syncData[$userId] = [
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],

                    // Preserve existing acceptance status
                    'acceptance_status' => $existingAcceptanceStatuses[$userId] ?? 'pending',
                ];
            }
        }

        $module->trainers()->sync($syncData);

        // Get NEW trainer IDs (ones that were added in this update)
        $newTrainerIds = array_diff(array_keys($syncData), $existingTrainerIds);

        // ONLY send notifications if training is ACTIVE
        if ($module->is_active == 1) {

            foreach ($newTrainerIds as $trainerId) {

                Notification::create([
                    'user_id' => $trainerId,
                    'title' => 'Training Session Assigned',
                    'message' => 'You have been assigned as trainer for: ' . $module->name,
                    'type' => 'trainer_assignment',
                    'training_id' => $module->id,
                ]);
            }
        }

        // Activity logging
        $oldTrainers = User::whereIn('id', $existingTrainerIds)
            ->pluck('name')
            ->toArray();

        $newTrainers = User::whereIn('id', array_keys($syncData))
            ->pluck('name')
            ->toArray();

        activity()
            ->performedOn($module)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => ['trainers' => $oldTrainers],
                'new' => ['trainers' => $newTrainers],
            ])
            ->log('Trainers assigned/updated');

        return back()->with('success', 'Trainers updated successfully.');
    }


    public function sendNotification($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read' => true
        ]);

        return back();
    }

    public function acceptTrainerTraining($trainingId)
    {
        $training = TrainingModule::findOrFail($trainingId);

        // Update the acceptance status
        $training->trainers()->updateExistingPivot(auth()->id(), [
            'acceptance_status' => 'accepted'
        ]);

        // Reload relation
        $training->load('acceptedTrainers');

        // Mark ALL unread notifications for this trainer and training as read
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('training_id', $trainingId)
            ->where('type', 'trainer_assignment')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true, 'message' => 'Training accepted successfully.']);
    }

    public function saveUsers(Request $request, $id)
    {
        $module = TrainingModule::findOrFail($id);

        // OLD trainees
        $oldUsers = $module->trainees()->pluck('name')->toArray();

        $syncData = [];

        if ($request->has('users')) {
            foreach ($request->users as $userData) {
                if (isset($userData['enrolled'])) {
                    $syncData[$userData['user_id']] = [
                        'start_date' => $userData['start_date'],
                        'end_date' => $userData['end_date'],
                        'status' => 'pending',
                    ];
                }
            }
        }

        // Get NEW trainee IDs
        $existingTraineeIds = $module->trainees()->pluck('users.id')->toArray();
        $module->trainees()->sync($syncData);
        $newTraineeIds = array_diff(array_keys($syncData), $existingTraineeIds);

        // ONLY send notifications if training is ACTIVE
        if ($module->is_active == 1) {
            foreach ($newTraineeIds as $userId) {
                \App\Models\Notification::create([
                    'user_id' => $userId,
                    'title' => 'New Training Assigned',
                    'message' => 'You have been assigned to training: ' . $module->name,
                    'type' => 'training_assigned',
                    'training_id' => $module->id,
                ]);
            }
        }

        // NEW trainees
        $newUsers = $module->trainees()->pluck('name')->toArray();

        // Activity log
        activity()
            ->performedOn($module)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => ['trainees' => $oldUsers],
                'new' => ['trainees' => $newUsers],
            ])
            ->log('Trainees assigned/updated');

        return back()->with('success', 'Trainee enrollment and individual dates updated.');
    }

    public function toggleStatus($id)
    {
        $training = TrainingModule::findOrFail($id);

        $oldStatus = $training->is_active;

        // Toggle the status
        $training->is_active = !$training->is_active;
        $training->updated_by = auth()->id();

        // If activating (changing from inactive to active)
        if ($training->is_active && !$oldStatus) {
            $training->activated_at = now();
            $training->activated_by = auth()->id();

            // ===== SEND NOTIFICATIONS TO TRAINERS =====
            foreach ($training->trainers as $trainer) {
                // Check if trainer hasn't already accepted
                $pivotData = $training->trainers()->where('user_id', $trainer->id)->first();
                if ($pivotData && $pivotData->pivot->acceptance_status !== 'accepted') {
                    \App\Models\Notification::create([
                        'user_id' => $trainer->id,
                        'title' => 'Training Session Assigned',
                        'message' => 'You have been assigned as trainer for: ' . $training->name,
                        'type' => 'trainer_assignment',
                        'training_id' => $training->id,
                    ]);
                }
            }

            // ===== SEND NOTIFICATIONS TO TRAINEES =====
            foreach ($training->trainees as $trainee) {
                \App\Models\Notification::create([
                    'user_id' => $trainee->id,
                    'title' => 'New Training Assigned',
                    'message' => 'You have been assigned to training: ' . $training->name,
                    'type' => 'training_assigned',
                    'training_id' => $training->id,
                ]);
            }
        }

        $training->save();

        // Log the activity
        activity()
            ->performedOn($training)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => ['status' => $oldStatus ? 'Active' : 'Inactive'],
                'attributes' => ['status' => $training->is_active ? 'Active' : 'Inactive'],
            ])
            ->log('status updated');

        $statusText = $training->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Training {$statusText} successfully! Notifications sent to all trainers and trainees.");
    }

    public function auditLogs($id)
    {
        $logs = Activity::where('subject_type', 'App\\Models\\TrainingModule')
            ->where('subject_id', $id)
            ->latest()
            ->get();

        return view('trainings.audit_logs', compact('logs'));
    }
    public function traininglist(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ("unauthorized user,user not found plz check");
        }
        //  $modules = $user->modules->pluck('name');
        $modules = $user->modules;
        return view('trainings.assign_training_list', compact('modules'));
    }
    public function traineeAttendace($id)
    {
        $user = Auth::user();

        if (!$user) {
            return "unauthorized user, user not found";
        }

        $module = TrainingModule::with(['documents', 'trainers'])->findOrFail($id);
        if (!$user->can('training-list') && !$user->modules()->where('training_modules.id', $module->id)->exists()) {
            abort(403, 'Unauthorized access to this module attendance sheet.');
        }

        $users = $module->trainees()
            ->where('users.is_trainer', 0)
            ->with(['department', 'designation'])
            ->orderBy('users.name')
            ->paginate(20)
            ->withQueryString();

        $latestSignedAttendance = $module->trainees()
            ->whereNotNull('training_user.attendance_marked_by')
            ->orderByDesc('training_user.attendance_marked_at')
            ->first();

        $attendanceSignerName = null;
        $attendanceSignedAt = null;

        if ($latestSignedAttendance && $latestSignedAttendance->pivot) {
            $attendanceSignerName = User::where('id', $latestSignedAttendance->pivot->attendance_marked_by)->value('name');
            $attendanceSignedAt = $latestSignedAttendance->pivot->attendance_marked_at;
        }

        return view('trainings.attendace_sheet', compact('users', 'module', 'attendanceSignerName', 'attendanceSignedAt'));
    }



    public function submitAttendace(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return "unauthorized user, user not found";
        }
        $module = TrainingModule::findOrFail($id);
        if (!$user->can('training-list') && !$user->modules()->where('training_modules.id', $module->id)->exists()) {
            abort(403, 'Unauthorized access to this module attendance sheet.');
        }

        $users = $module->trainees()
            ->where('users.is_trainer', 0)
            ->with(['department', 'designation'])
            ->orderBy('users.name')
            ->paginate(20)
            ->withQueryString();

        $validated = $request->validate([
            'listed_user_ids' => 'required|array|min:1',
            'listed_user_ids.*' => 'integer|exists:users,id',
            'attendance' => 'nullable|array',
            'attendance.*' => 'in:0,1',
            'session_brief_type' => 'required|string|in:SOP,STP,Protocol,Others',
            'session_comments' => 'nullable|string|max:1000',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $listedUserIds = collect($validated['listed_user_ids'])->map(fn($userId) => (int) $userId)->unique()->values();
        $enrolledUserIds = $module->trainees()
            ->whereIn('users.id', $listedUserIds)
            ->pluck('users.id')
            ->map(fn($userId) => (int) $userId)
            ->values();
        $attendanceMap = $validated['attendance'] ?? [];
        $trainerId = optional($module->trainers()->first())->id ?? $user->id;
        $sessionTopic = $module->name . ' - ' . $validated['session_brief_type'];

        // Update only trainees that belong to this module.
        $submittedAt = now();
        foreach ($enrolledUserIds as $userId) {
            $isPresent = isset($attendanceMap[$userId]) && (string) $attendanceMap[$userId] === '1';
            $module->trainees()->updateExistingPivot($userId, [
                'attendance_status' => $isPresent ? 'present' : 'absent',
                'attendance_marked_at' => $submittedAt,
                'attendance_marked_by' => $user->id,
            ]);



            $payload = [
                'training_date' => Carbon::now()->format('Y-m-d'),
                'trainee_id' => $userId,
                'trainer_id' => $trainerId,
                'topic' => $sessionTopic,
                'register_no' => 'N/A',
                'page_no' => 'N/A',
                'session_brief_type' => $validated['session_brief_type'],
                'session_comments' => $validated['session_comments'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ];

            TrainingSessions::updateOrCreate(
                [
                    'training_date' => $payload['training_date'],
                    'trainee_id' => $payload['trainee_id'],
                    'trainer_id' => $payload['trainer_id'],
                ],
                $payload
            );
        }

        return redirect()
            ->route('attendance', ['id' => $module->id, 'page' => $request->query('page')])
            ->with('success', 'Attendance submitted successfully.');
    }

    public function annual_training(Request $request)
    {

        return view('trainings.annual_training');

    }
}
