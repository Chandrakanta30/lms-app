<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;

use App\Models\TrainingDocument;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

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
            'status' => 'required|in:' . implode(',', TrainingModule::STATUSES),
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
                if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
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
            'status' => 'required|in:' . implode(',', TrainingModule::STATUSES),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'step_names' => 'nullable|array',
            'step_names.*' => 'nullable|string|max:255',
            'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
        ]);

        $training->update([
            'name' => $request->name,
            'training_type' => $request->training_type,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'updated_by' => auth()->id(),
        ]);

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

        if ($request->training_type === 'self_training' && $request->has('docs')) {
            foreach ($request->docs as $doc) {
                if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $doc['file']->store('training_materials', 'public');

                    TrainingDocument::create([
                        'training_id' => $training->id,
                        'doc_type' => $doc['type'],
                        'doc_name' => $doc['name'],
                        'doc_number' => $doc['number'] ?? 'N/A',
                        'doc_version' => $doc['version'] ?? 'v1.0',
                        'file_path' => $path,
                    ]);
                }
            }
        }

        return redirect()->route('trainings.index')->with('success', 'Training updated successfully.');
    }

    public function destroy(TrainingModule $training)
    {
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

        return back()->with('success', 'Trainers updated.');
    }

    public function saveUsers(Request $request, $id)
    {
        $module = TrainingModule::findOrFail($id);
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

        return back()->with('success', 'Trainee enrollment and individual dates updated.');
    }

    public function toggleStatus($id)
    {
        $training = TrainingModule::findOrFail($id);

        $training->is_active = !$training->is_active;
        $training->updated_by = auth()->id();

        if ($training->is_active) {
            $training->activated_at = now();
            $training->activated_by = auth()->id();
        }

        $training->save();

        $status = $training->is_active ? 'Activated' : 'Deactivated';

        return back()->with('success', "Training {$status} successfully!");
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
    }

    return redirect()
        ->route('attendance', ['id' => $module->id, 'page' => $request->query('page')])
        ->with('success', 'Attendance submitted successfully.');
}
}
