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
        $module = TrainingModule::with('documents')->findOrFail($moduleId);

        $isAnnual = (string) $module->is_anuual === '1';
        $subdepartmentIds = array_values(array_filter($module->subdepartment_id ?? []));
        $hasDeptAndSubdept = !empty($module->department_id) && !empty($subdepartmentIds);


        $allDocsQuery = MasterDocument::withCount('questions');
        if ($isAnnual) {
            $allDocsQuery = $allDocsQuery->whereRaw('1 = 0');

            if ($hasDeptAndSubdept) {
                $allDocsQuery = MasterDocument::withCount('questions')
                    ->where('department_id', $module->department_id)
                    ->whereIn('subdepartment_id', $subdepartmentIds);
            }
        }


        if ($isAnnual && $hasDeptAndSubdept) {
            $matchingDocs = (clone $allDocsQuery)->get();

            $syncData = [];
            foreach ($matchingDocs as $doc) {

                $syncData[$doc->id] = ['question_quota' => 1];
            }

            if (!empty($syncData)) {
                $module->documents()->syncWithoutDetaching($syncData);
                $module->load('documents');
            }
        }

        $allDocs = $allDocsQuery->get();

        return view('trainings.link_docs', compact('module', 'allDocs'));
    }


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
