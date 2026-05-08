<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;

use App\Models\TrainingDocument;
use App\Models\TrainingModule;
use App\Models\TrainingSessions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class TrainingModuleController extends Controller
{
    public function index()
    {
        $trainings = TrainingModule::with(['steps', 'trainers.designation', 'trainees.designation', 'documents'])
            ->whereNull('parent_id')
            ->latest('id')
            ->get();

        $statusOptions = TrainingModule::STATUSES;

        return view('trainings.index', compact('trainings', 'statusOptions'));
    }

    public function create()
    {
        $statusOptions = TrainingModule::STATUSES;

        return view('trainings.create', compact('statusOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'training_type' => 'required|in:classroom,self_training',
            'status' => 'required|in:'.implode(',', TrainingModule::STATUSES),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
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
            'parent_id' => null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'is_active' => true,
            'activated_at' => now(),
            'activated_by' => auth()->id(),
        ]);

        foreach (array_values(array_filter($request->input('step_names', []), fn ($stepName) => filled($stepName))) as $index => $stepName) {
            $parent->steps()->create([
                'name' => $stepName,
                'step_number' => $index + 1,
                'training_type' => $request->training_type,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
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

        activity()
            ->performedOn($parent)
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => [
                    'name' => $parent->name,
                    'status' => $parent->status,
                    'training_type' => $parent->training_type,
                ],
            ])
            ->log('created');

        return redirect()->route('trainings.index')->with('success', 'Training program and materials created successfully.');
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
        $user = auth()->user();

        // Add back based on role
        if ($user->hasRole('Reviewer')) {
            $statusOptions[] = 'reviewed';
        }

        if ($user->hasRole('Approver')) {
            $statusOptions[] = 'approved';
        }

        return view('trainings.edit', compact('training', 'statusOptions'));
    }

    public function update(Request $request, TrainingModule $training)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'training_type' => 'required|in:classroom,self_training',
            'status' => 'required|in:'.implode(',', TrainingModule::STATUSES),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
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
        ]);

        $training->update([
            'name' => $request->name,
            'training_type' => $request->training_type,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'updated_by' => auth()->id(),
        ]);

        $newData = $training->only([
            'name',
            'training_type',
            'status',
            'start_date',
            'end_date',
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

        foreach (array_values(array_filter($request->input('step_names', []), fn ($stepName) => filled($stepName))) as $index => $stepName) {
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

            if (! empty($docNames)) {
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
        $module = TrainingModule::with('trainers')->findOrFail($id);
        $allUsers = User::orderBy('name', 'asc')->where('is_trainer', 1)->get();

        return view('trainings.assign_trainers', compact('module', 'allUsers'));
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

        // OLD trainers (names)
        $oldTrainers = $module->trainers()->pluck('name')->toArray();

        $syncData = [];

        if ($request->has('trainers')) {
            foreach ($request->trainers as $data) {
                $syncData[$data['user_id']] = [
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                ];
            }
        }

        $module->trainers()->sync($syncData);

        // NEW trainers (names)
        $newTrainers = $module->trainers()->pluck('name')->toArray();

        activity()
            ->performedOn($module)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => ['trainers' => $oldTrainers],
                'new' => ['trainers' => $newTrainers],
            ])
            ->log('Trainers assigned/updated');

        return back()->with('success', 'Trainers updated.');
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

        $module->trainees()->sync($syncData);

        // NEW trainees
        $newUsers = $module->trainees()->pluck('name')->toArray();

        // ✅ LOG
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

        $training->is_active = ! $training->is_active;
        $training->updated_by = auth()->id();

        if ($training->is_active) {
            $training->activated_at = now();
            $training->activated_by = auth()->id();
        }

        $training->save();

        $newStatus = $training->is_active;

        activity()
            ->performedOn($training)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => ['status' => $oldStatus ? 'Active' : 'Inactive'],
                'attributes' => ['status' => $newStatus ? 'Active' : 'Inactive'],
            ])
            ->log('status updated');

        return back()->with('success', 'Training status updated!');
    }

    public function auditLogs($id)
    {
        $logs = Activity::where('subject_type', 'App\\Models\\TrainingModule')
            ->where('subject_id', $id)
            ->latest()
            ->get();

    return view('trainings.audit_logs', compact('logs'));
}
  public function traininglist(Request $request){
           $user=Auth::user();
    if(!$user){
        return("unauthorized user,user not found plz check");
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
        abort(403, 'Unauthorized access to submit this module attendance.');
    }

    $validated = $request->validate([
        'listed_user_ids' => 'required|array|min:1',
        'listed_user_ids.*' => 'integer|exists:users,id',
        'attendance' => 'nullable|array',
        'attendance.*' => 'in:0,1',
    ]);

    $listedUserIds = collect($validated['listed_user_ids'])->map(fn ($userId) => (int) $userId)->unique()->values();
    $enrolledUserIds = $module->trainees()
        ->whereIn('users.id', $listedUserIds)
        ->pluck('users.id')
        ->map(fn ($userId) => (int) $userId)
        ->values();
    $attendanceMap = $validated['attendance'] ?? [];

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
            'trainee_id'    => $userId,
            'trainer_id'    => $module->trainers()->firstOrFail()->id,
            'topic'         => 'Laravel Validation and Eloquent Basics',
            'register_no'   => 'REG-2026-001',
            'page_no'       => '45',
        ];
        
        TrainingSessions::updateOrCreate(
            [
                'training_date' => $payload['training_date'],
                'trainee_id'    => $payload['trainee_id'],
                'trainer_id'    => $payload['trainer_id'],
            ],
            $payload
        );



        
    }

    return redirect()
        ->route('attendance', ['id' => $module->id, 'page' => $request->query('page')])
        ->with('success', 'Attendance submitted successfully.');
}
}
