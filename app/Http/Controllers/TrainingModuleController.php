<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrainingModule;
use App\Models\TrainingDocument;
use App\Models\User;
class TrainingModuleController extends Controller
{

    public function index()
    {
        // Only get parent trainings (where parent_id is null)
        $trainings = TrainingModule::with(['steps', 'trainers', 'trainees', 'documents'])->whereNull('parent_id')->with('steps')->get();
        return view('trainings.index', compact('trainings'));
    }

    public function create()
    {
        return view('trainings.create');
    }

    public function store(Request $request)
    {
        // Validate both the Training data and the optional Documents
        $request->validate([
            'name' => 'required|string|max:255',
            'training_type' => 'required|in:classroom,self_training',
            // 'step_names' => 'required|array',
            // 'step_names.*' => 'required|string',
            // Document validation (only if self_training)
            'docs.*.type' => 'required_if:training_type,self_training|in:SOP,Protocol,PPT,Others',
            'docs.*.name' => 'required_if:training_type,self_training',
            'docs.*.file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
        ]);
    

        // dd($request->all());

        // 1. Create the Parent Training Module
        $parent = TrainingModule::create([
            'name' => $request->name,
            'training_type' => $request->training_type, // Saved the column we added
            'parent_id' => null,
            'created_by' => auth()->id(), // Track Creator
            'is_active' => true,
            'activated_at' => now(),
            'activated_by' => auth()->id()
        ]);
    
        // 2. Create the Children (Steps)
        foreach ($request->step_names as $index => $stepName) {
            $parent->steps()->create([
                'name' => $stepName,
                'step_number' => $index + 1
            ]);
        }
    
        // 3. Handle Self-Training Documents
        if ($request->training_type == 'self_training' && $request->has('docs')) {
            foreach ($request->docs as $doc) {
                if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
                    
                    // Store file and get path
                    $path = $doc['file']->store('training_materials', 'public');
    
                    // Use $parent->id to link the documents
                    TrainingDocument::create([
                        'training_id' => $parent->id, 
                        'doc_type'    => $doc['type'],
                        'doc_name'    => $doc['name'],
                        'doc_number'  => $doc['number'] ?? 'N/A',
                        'doc_version' => $doc['version'] ?? 'v1.0',
                        'file_path'   => $path
                    ]);
                }
            }
        }
    
        return redirect()->route('trainings.index')->with('success', 'Training Program and Materials Created!');
    }

    public function edit(TrainingModule $training)
    {
        // Eager load steps and documents to show them in the edit form
        $training->load(['steps', 'documents']);
        
        return view('trainings.edit', compact('training'));
    }

    public function update(Request $request, TrainingModule $training)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'training_type' => 'required|in:classroom,self_training',
        'step_names' => 'required|array',
        'docs.*.type' => 'required_if:training_type,self_training',
    ]);

    // 1. Update Parent Details
    $training->update([
        'name' => $request->name,
        'training_type' => $request->training_type,
        'updated_by' => auth()->id()
    ]);

    // 2. Update Steps (Flush and Recreate is fine for simple ordering)
    $training->steps()->delete();
    foreach ($request->step_names as $index => $stepName) {
        $training->steps()->create([
            'name' => $stepName,
            'step_number' => $index + 1
        ]);
    }

    // 3. Handle Documents
    if ($request->training_type == 'self_training' && $request->has('docs')) {
        foreach ($request->docs as $doc) {
            // Only create new records if a file is actually uploaded
            // Existing files are already in the DB; this logic handles NEW uploads
            if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
                
                $path = $doc['file']->store('training_materials', 'public');

                TrainingDocument::create([
                    'training_id' => $training->id,
                    'doc_type'    => $doc['type'],
                    'doc_name'    => $doc['name'],
                    'doc_number'  => $doc['number'] ?? 'N/A',
                    'doc_version' => $doc['version'] ?? 'v1.0',
                    'file_path'   => $path
                ]);
            }
        }
    }

    return redirect()->route('trainings.index')->with('success', 'Training Updated Successfully!');
}

    public function destroy(TrainingModule $training)
    {
        $training->delete(); // Cascade delete handles the steps
        return redirect()->route('trainings.index')->with('success', 'Training Deleted!');
    }



    public function manageTrainers($id)
    {
        // Fetch the module with its currently assigned trainers (from pivot table)
        $module = TrainingModule::with('trainers')->findOrFail($id);
        
        // Fetch all users so we can pick them as trainers
        $allUsers = User::orderBy('name', 'asc')->where('is_trainer', 1)->get();

        return view('trainings.assign_trainers', compact('module', 'allUsers'));
    }

    /**
     * Show the page to manage Trainees (Users) for a specific module
     */
    public function manageUsers($id)
    {
        // Fetch the module with its currently enrolled trainees
        $module = TrainingModule::with('trainees')->findOrFail($id);
        
        // Fetch all users to show in the enrollment checklist
        $allUsers = User::orderBy('name', 'asc')->get();

        return view('trainings.assign_users', compact('module', 'allUsers'));
    }



    public function saveTrainers(Request $request, $id) {
        $module = TrainingModule::findOrFail($id);
        $syncData = [];
        
        if($request->has('trainers')) {
            foreach($request->trainers as $data) {
                $syncData[$data['user_id']] = [
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date']
                ];
            }
        }
        
        $module->trainers()->sync($syncData);
        return back()->with('success', 'Trainers updated.');
    }
    
    // Save Users (Trainees)
    public function saveUsers(Request $request, $id) {
        $module = TrainingModule::findOrFail($id);
        $syncData = [];
    
        if ($request->has('users')) {
            foreach ($request->users as $userData) {
                // Only sync users that have the 'enrolled' checkbox checked
                if (isset($userData['enrolled'])) {
                    $syncData[$userData['user_id']] = [
                        'start_date' => $userData['start_date'],
                        'end_date'   => $userData['end_date'],
                        // You can also default the status here
                        'status'     => 'pending' 
                    ];
                }
            }
        }
    
        // sync() removes anyone not in the $syncData array and updates/adds the rest
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
        return back()->with('success', "Training $status successfully!");
    }

   
}
