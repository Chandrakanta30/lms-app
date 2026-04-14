<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterDocument;
use App\Models\MasterQuestion;
use Illuminate\Support\Facades\DB;

class MasterQuestionController extends Controller
{
    public function index($docId)
    {
        // Fetch the document and its existing questions
        $document = MasterDocument::with('questions')->findOrFail($docId);
        
        return view('questions.master_manage', compact('document'));
    }

    /**
     * Sync the question pool (Bulk Create/Update/Delete).
     */
    public function sync(Request $request, $docId)
    {
        $document = MasterDocument::findOrFail($docId);

        $document->questions()->delete();

        if ($request->has('questions')) {
            foreach ($request->questions as $qData) {
                $document->questions()->create([
                    'question_text'  => $qData['question_text'],
                    'question_type'  => $qData['question_type'],
                    'correct_answer' => $qData['correct_answer'],
                    // Convert "A,B,C" into ["A", "B", "C"]
                    'options'        => ($qData['question_type'] === 'mcq') 
                                        ? array_map('trim', explode(',', $qData['options'])) 
                                        : null,
                ]);
            }
        }

        return redirect()->route('master-documents.index')
                         ->with('success', 'Question pool updated for ' . $document->doc_name);
    }

    /**
     * Remove all questions for a document (Reset Pool).
     */
    public function clearPool($docId)
    {
        MasterQuestion::where('master_document_id', $docId)->delete();
        
        return back()->with('success', 'Question pool cleared successfully.');
    }
}
