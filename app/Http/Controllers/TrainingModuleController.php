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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingModuleController extends Controller
{
    private function statusFilterForRole($user): array
    {
        $isAdmin = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'super-admin']);

        if ($isAdmin) {
            return [];
        }

        $allowed = [];

        if ($user->hasRole('Reviewer')) {
            $allowed[] = 'inreview';
        }

        if ($user->hasRole('Approver')) {
            $allowed[] = 'reviewed';
        }

        return $allowed;
    }

    public function index(Request $request)
    {
        $query = TrainingModule::with([
            'steps',
            'trainers.designation',
            'trainees.designation',
            'documents'
        ])->whereNull('parent_id')
            ->where(function ($query) {
                $query->whereNull('is_anuual')
                    ->orWhere('is_anuual', '0');
            });

        if (request()->route()->getName() === 'created-training-setup') {
            $query = $query->where('is_active', 1);
        } else {
            $query = $query->where('is_active', 0);
        }

        $statusFilter = $this->statusFilterForRole(auth()->user());
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $trainings = $query->latest('id')->paginate(10)->withQueryString();

        $statusOptions = TrainingModule::STATUSES;

        return view('trainings.index', compact('trainings', 'statusOptions'));
    }

    public function annualTrainingIndex(Request $request)
    {
        $query = TrainingModule::withCount('steps')
            ->where('is_anuual', '1')
            ->where('is_active', 0)
            ->where(function ($q) {
                $q->whereNull('annual_parent_id')->orWhere('annual_parent_id', 0);
            });

        $statusFilter = $this->statusFilterForRole(auth()->user());
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $trainings = $query->latest('id')->paginate(10)->withQueryString();

        return view('trainings.annual_training', compact('trainings'));
    }

    public function createdAnnualTrainingIndex(Request $request)
    {
        $query = TrainingModule::with([
            'steps',
            'trainers.designation',
            'trainees.designation',
            'documents'
        ])
            ->where('is_anuual', '1')
            ->where('is_active', 1)
            ->whereNotNull('annual_parent_id');

        $statusFilter = $this->statusFilterForRole(auth()->user());
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $trainings = $query->latest('id')->paginate(10)->withQueryString();

        $statusOptions = TrainingModule::STATUSES;

        return view('trainings.annual_training', compact('trainings', 'statusOptions'));
    }

    public function annualTrainingPrograms($parentId)
    {
        $parentPlan = TrainingModule::where('id', $parentId)
            ->where('is_anuual', '1')
            ->where('is_active', 0)
            ->firstOrFail();

        $query = TrainingModule::with([
            'steps',
            'trainers.designation',
            'trainees.designation',
            'documents'
        ])->where('annual_parent_id', $parentPlan->id)
            ->where('is_anuual', '1')
            ->where('is_active', 0);

        $statusFilter = $this->statusFilterForRole(auth()->user());
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        $trainings = $query->latest('id')->get();

        $statusOptions = TrainingModule::STATUSES;
        $backUrl = route('annual-training');

        return view('trainings.annual_training', compact('trainings', 'statusOptions', 'parentPlan', 'backUrl'));
    }

    public function createdAnnualTrainingPrograms($parentId)
    {
        $parentPlan = TrainingModule::where('id', $parentId)
            ->where('is_anuual', '1')
            ->where('is_active', 1)
            ->firstOrFail();

        $query = TrainingModule::with([
            'steps',
            'trainers.designation',
            'trainees.designation',
            'documents'
        ])->where('annual_parent_id', $parentPlan->id)
            ->where('is_anuual', '1')
            ->where('is_active', 1);

        $statusFilter = $this->statusFilterForRole(auth()->user());
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        $trainings = $query->latest('id')->get();

        $statusOptions = TrainingModule::STATUSES;
        $backUrl = route('created-annual-training');

        return view('trainings.annual_training', compact('trainings', 'statusOptions', 'parentPlan', 'backUrl'));
    }

    public function create()
    {
        $statusOptions = TrainingModule::STATUSES;
        $departments = Department::all();
        $subdepartments = SubDepartment::all();
        $formTokenKey = 'training_form_token_classic';
        $formToken = $this->getTrainingFormToken($formTokenKey);

        return view('trainings.create', compact('statusOptions', 'departments', 'subdepartments', 'formToken', 'formTokenKey'));
    }

    public function createAnnual()
    {
        $statusOptions = TrainingModule::STATUSES;
        $departments = Department::all();
        $subdepartments = SubDepartment::all();
        $formTokenKey = 'training_form_token_annual';
        $formToken = $this->getTrainingFormToken($formTokenKey);

        return view('trainings.annual_Program_create', compact('statusOptions', 'departments', 'subdepartments', 'formToken', 'formTokenKey'));
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
        $formTokenKey = $request->input('is_annual') == '1'
            ? 'training_form_token_annual'
            : 'training_form_token_classic';
        $sessionToken = session($formTokenKey);
        $submittedToken = $request->input('form_token');

        if (empty($submittedToken) || empty($sessionToken) || !hash_equals((string) $sessionToken, (string) $submittedToken)) {
            return redirect()->back()->with('error', 'This training form was already submitted. Please open the page again and try once more.');
        }

        session()->forget($formTokenKey);

        try {
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
                'subdepartment_id' => 'nullable',
                'subdepartment_id.*' => 'integer|exists:sub_departments,id',

                'step_names' => 'nullable|array',
                'step_names.*' => 'nullable|string|max:255',

                'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
                'docs.*.name' => 'required_if:training_type,self_training',
                'docs.*.file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
            ]);
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', implode(' ', $e->validator->errors()->all()));
        }

        if ($request->status !== 'created') {
            return back()->withInput()->with('error', 'A new training program must be created with status "Created". It can only move to In Review, Reviewed, or Approved after documents, trainers, and trainees have been added.');
        }

        $subdepartmentIds = $this->normalizeSubdepartmentIds($request->input('subdepartment_id'));

        try {
            $parent = DB::transaction(function () use ($request, $subdepartmentIds) {
                return $this->createTrainingProgram($request, $subdepartmentIds);
            });
        } catch (\Throwable $e) {
            report($e);

            $label = $request->input('is_annual') == '1' ? 'annual training program' : 'training program';

            return back()->withInput()->with('error', "Failed to create the {$label}: " . $e->getMessage());
        }

        if ($request->input('is_annual') == '1') {
            return redirect()->route('annual-training')
                ->with('success', 'Annual training program created successfully.');
        }

        return redirect()->route('trainings.index')
            ->with('success', 'Training program created successfully.');
    }

    private function createTrainingProgram(Request $request, array $subdepartmentIds): TrainingModule
    {
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
            'subdepartment_id' => $subdepartmentIds ?: null,

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
                'monthly' => ['count' => 12, 'gap' => 1],
                'quarterly' => ['count' => 3, 'gap' => 4],
                'half_yearly' => ['count' => 2, 'gap' => 6],
                'yearly' => ['count' => 1, 'gap' => 12],
            ];

            $config = $frequencyMap[$request->frequency] ?? ['count' => 0, 'gap' => 1];

            $count = $config['count'];
            $gap = $config['gap'];

            for ($i = 0; $i < $count; $i++) {

                $monthDate = now()->addMonths($i * $gap);
                $monthName = $monthDate->format('F');

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

                $this->autoEnrollMatchingUsersToTraining($child);
            }
        }

        $this->autoEnrollMatchingUsersToTraining($parent);
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

        return $parent;
    }

    private function getTrainingFormToken(string $formTokenKey): string
    {
        if (!session()->has($formTokenKey)) {
            session([$formTokenKey => (string) Str::uuid()]);
        }

        return (string) session($formTokenKey);
    }

    private function autoEnrollMatchingUsersToTraining(TrainingModule $training): void
    {
        $isDqaTraining = Department::whereKey($training->department_id)
            ->where('name', 'Development Quality Assurance')
            ->exists();

        $trainingSubdepartmentIds = $this->normalizeSubdepartmentIds($training->subdepartment_id);

        if (!$isDqaTraining && (empty($training->department_id) || empty($trainingSubdepartmentIds))) {
            return;
        }

        $matchingUserIds = User::query()
            ->where('is_trainer', 0)
            ->when(!$isDqaTraining, function ($query) use ($training) {
                $query->where('department_id', $training->department_id)
                    ->whereIn('subdepartment_id', $this->normalizeSubdepartmentIds($training->subdepartment_id));
            })
            ->pluck('id')
            ->toArray();

        if (empty($matchingUserIds)) {
            return;
        }

        $syncData = [];
        foreach ($matchingUserIds as $userId) {
            $syncData[$userId] = [
                'status' => 'pending',
                'start_date' => null,
                'end_date' => null,
            ];
        }

        $training->trainees()->syncWithoutDetaching($syncData);
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
        $departments = Department::all();
        $subdepartments = SubDepartment::all();
        $user = auth()->user();
        $isAdmin = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'super-admin']);

        if ($isAdmin) {
            $statusOptions = TrainingModule::STATUSES;
        } else {
            $statusOptions = array_diff(TrainingModule::STATUSES, ['approved', 'reviewed']);

            // Add back based on role
            if ($user->hasRole('Reviewer')) {
                $statusOptions[] = 'reviewed';
            }

            if ($user->hasRole('Approver')) {
                $statusOptions[] = 'approved';
            }

            if (!in_array($training->status, $statusOptions, true)) {
                $statusOptions[] = $training->status;
            }
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
            'subdepartment_id' => 'nullable',
            'subdepartment_id.*' => 'integer|exists:sub_departments,id',
            'step_names' => 'nullable|array',
            'step_names.*' => 'nullable|string|max:255',
            'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
        ]);

        if ($training->status === 'created' && $request->status !== 'created') {
            $missing = [];

            $hasDocuments = $training->training_type === 'self_training'
                ? $this->hasSelfTrainingDocuments($training)
                : $training->documents()->exists();

            if (!$hasDocuments) {
                $missing[] = 'at least one document must be added';
            }

            if ($training->training_type !== 'self_training' && !$training->trainers()->exists()) {
                $missing[] = 'at least one trainer must be added';
            }

            if (!$training->trainees()->exists()) {
                $missing[] = 'at least one trainee must be added';
            }

            if (!empty($missing)) {
                return back()
                    ->withInput()
                    ->with('error', 'Cannot change status from Created: ' . ucfirst(implode(', ', $missing)) . '.');
            }
        }

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
            $updateData['frequency'] = $request->frequency;
            $updateData['department_id'] = $request->department_id;
            $updateData['subdepartment_id'] = $this->normalizeSubdepartmentIds($request->input('subdepartment_id')) ?: null;
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

    private function normalizeSubdepartmentIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }

        return filled($value) ? [(int) $value] : [];
    }

    private function hasSelfTrainingDocuments(TrainingModule $training): bool
    {
        return TrainingDocument::where('training_id', $training->id)->exists()
            || $training->documents()->exists();
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

        $backUrl = $this->resolveTrainingBackUrl(
            'trainings.manage_trainers_back_url',
            route('manage-trainers', $id),
            route('trainings.index')
        );

        return view('trainings.assign_trainers', compact('module', 'allUsers', 'allVenues', 'backUrl'));
    }

    public function manageUsers($id)
    {
        $module = TrainingModule::with('trainees')->findOrFail($id);
        $allUsers = User::orderBy('name', 'asc')->get();

        $backUrl = $this->resolveTrainingBackUrl(
            'trainings.manage_users_back_url',
            route('manage-users', $id),
            route('trainings.index')
        );

        return view('trainings.assign_users', compact('module', 'allUsers', 'backUrl'));
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

    private function resolveTrainingBackUrl(string $sessionKey, string $currentUrl, string $fallbackUrl): string
    {
        $previousUrl = url()->previous();

        if (!empty($previousUrl) && $previousUrl !== $currentUrl) {
            session([$sessionKey => $previousUrl]);

            return $previousUrl;
        }

        $storedBackUrl = session($sessionKey);

        if (!empty($storedBackUrl) && $storedBackUrl !== $currentUrl) {
            return $storedBackUrl;
        }

        return $fallbackUrl;
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

        // Only allow activating a training once it has been approved
        if (!$oldStatus && $training->status !== 'approved') {
            return back()->with('error', 'This training cannot be activated until it is Approved.');
        }

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
        if ((int) $user->is_trainer === 1) {
            $modules = $user->modules()
                ->wherePivot('acceptance_status', 'accepted')
                ->get();
        } else {
            $modules = $user->trainings;
        }
        return view('trainings.assign_training_list', compact('modules'));
    }

    public function calendar()
    {
        return view('trainings.calendar');
    }

    public function calendarEvents(Request $request)
    {
        $year = (int) ($request->input('year') ?: now()->year);
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $modules = TrainingModule::query()
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $yearEnd->toDateString())
            ->whereDate('end_date', '>=', $yearStart->toDateString())
            ->get(['id', 'name', 'status', 'start_date', 'end_date', 'is_active']);

        $events = $modules->map(function ($module) {
            return [
                'id' => $module->id,
                'title' => $module->name,
                'start' => $module->start_date,
                'end' => Carbon::parse($module->end_date)->addDay()->toDateString(),
                'allDay' => true,
                'url' => route('trainings.show', $module->id),
                'extendedProps' => [
                    'status' => $module->status,
                    'is_active' => (int) $module->is_active,
                ],
            ];
        });

        return response()->json($events);
    }
    public function traineeAttendace($id)
    {
        $user = Auth::user();

        if (!$user) {
            return "unauthorized user, user not found";
        }

        $module = TrainingModule::with(['documents', 'trainers'])->findOrFail($id);
        $isAssignedTrainee = $user->trainings()->where('training_modules.id', $module->id)->exists();
        $isAcceptedTrainer = $module->trainers()
            ->where('users.id', $user->id)
            ->wherePivot('acceptance_status', 'accepted')
            ->exists();

        if (
            !$user->can('training-list')
            && !$isAssignedTrainee
            && !$isAcceptedTrainer
        ) {
            abort(403, 'Unauthorized access to this module attendance sheet.');
        }

        $users = $module->trainees()
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

        $backUrl = $this->resolveTrainingBackUrl(
            'trainings.attendance_sheet_back_url',
            route('attendance', $id),
            route('training-list')
        );

        return view('trainings.attendace_sheet', compact('users', 'module', 'attendanceSignerName', 'attendanceSignedAt', 'backUrl'));
    }



    public function submitAttendace(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return "unauthorized user, user not found";
        }
        $module = TrainingModule::findOrFail($id);
        $isAssignedTrainee = $user->trainings()->where('training_modules.id', $module->id)->exists();
        $isAcceptedTrainer = $module->trainers()
            ->where('users.id', $user->id)
            ->wherePivot('acceptance_status', 'accepted')
            ->exists();

        if (
            !$user->can('training-list')
            && !$isAssignedTrainee
            && !$isAcceptedTrainer
        ) {
            abort(403, 'Unauthorized access to this module attendance sheet.');
        }

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
                'training_module_id' => $module->id,
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
                    'trainee_id' => $payload['trainee_id'],
                    'training_module_id' => $payload['training_module_id'],
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
