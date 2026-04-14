<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrainingModule;
use App\Models\MasterDocument;
use Illuminate\Support\Facades\DB;

class ModuleLinkController extends Controller
{
    public function showLinkPage($moduleId)
    {
        // 1. Get the specific training module with its currently linked documents
        $module = TrainingModule::with('documents')->findOrFail($moduleId);

        // 2. Get all global documents from the Master Pool with their question counts
        $allDocs = MasterDocument::withCount('questions')->get();
        
        return view('trainings.link_docs', compact('module', 'allDocs'));
    }

    /**
     * Save the linked documents and their respective random question quotas.
     */
    public function saveLinks(Request $request, $moduleId)
    {
        $module = TrainingModule::findOrFail($moduleId);
        
        $syncData = [];
    
        if ($request->has('docs')) {
            foreach ($request->docs as $docId => $data) {
                // Only add to the exam if the 'Select' checkbox was checked
                if (isset($data['selected'])) {
                    $syncData[$docId] = [
                        'question_quota' => $data['quota'] ?? 0
                    ];
                }
            }
        }
    
        // sync() removes unchecked docs and updates/adds checked ones
        $module->documents()->sync($syncData);
    
        return redirect()->route('trainings.index')->with('success', 'Exam configuration saved successfully!');
    }

    /**
     * Optional: API/Helper to check total questions for a module
     */
    public function getModuleSummary($moduleId)
    {
        $module = TrainingModule::with('documents')->findOrFail($moduleId);
        $totalQuestions = $module->documents->sum('pivot.question_quota');

        return response()->json([
            'module_name' => $module->name,
            'total_exam_questions' => $totalQuestions,
            'linked_docs_count' => $module->documents->count()
        ]);
    }
}
