<?php

namespace App\Http\Controllers;

use App\Models\TrainingModule;
use App\Models\ExamResult;
use Auth;
use Illuminate\Http\Request;
use DB;
class QuestionController extends Controller
{
    public function index($moduleId)
    {
        $module = TrainingModule::with('questions')->findOrFail($moduleId);
        return view('questions.manage', compact('module'));
    }

    /**
     * Bulk Store/Update questions
     */
    public function sync(Request $request, $moduleId)
    {
        $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.correct_answer' => 'required|in:Yes,No',
        ]);

        $module = TrainingModule::findOrFail($moduleId);

        DB::transaction(function () use ($request, $module) {
            // Remove old questions to sync fresh
            $module->questions()->delete();

            foreach ($request->questions as $qData) {
                $module->questions()->create([
                    'question_text' => $qData['question_text'],
                    'correct_answer' => $qData['correct_answer'],
                ]);
            }
        });

        return redirect()->route('trainings.index')
                         ->with('success', 'Question paper updated for ' . $module->name);
    }


    public function takeExam($moduleId)
    {
        $module = TrainingModule::with('documents')->findOrFail($moduleId);
        $examPaper = collect();
    
        foreach ($module->documents as $doc) {
            $quota = $doc->pivot->question_quota;
            
            // Pick random questions from the Master Pool of this document
            $randomQuestions = \App\Models\MasterQuestion::where('master_document_id', $doc->id)
                                ->inRandomOrder()
                                ->limit($quota)
                                ->get();
            
            $examPaper = $examPaper->concat($randomQuestions);
        }
    
        // Shuffle final list so questions from different SOPs are mixed
        $examPaper = $examPaper->shuffle();

        // dd($module);

    
        return view('exams.take', compact('module', 'examPaper'));
    }



    public function submitExam(Request $request, $moduleId)
{
    // 1. Validate that answers were actually sent
    $request->validate([
        'answers' => 'required|array',
    ]);

    $module = TrainingModule::findOrFail($moduleId);
    $userAnswers = $request->input('answers'); // Format: [question_id => "Yes/No"]
    $questionIds = array_keys($userAnswers);

    // 2. Fetch only the questions that were in the user's exam paper
    // This ensures we grade against the correct pool
    $questions = \App\Models\MasterQuestion::whereIn('id', $questionIds)->get();

    $totalQuestions = $questions->count();
    $correctCount = 0;
    $details = []; // Optional: To store which specific ones they got wrong

    // 3. Compare User Answer vs Master Answer
    foreach ($questions as $question) {
        $submittedAnswer = $userAnswers[$question->id] ?? null;
        $isCorrect = ($submittedAnswer === $question->correct_answer);

        if ($isCorrect) {
            $correctCount++;
        }

        // Store details for an "Audit Trail" or Review Page
        $details[] = [
            'question_text' => $question->question_text,
            'user_answer'   => $submittedAnswer,
            'actual_answer' => $question->correct_answer,
            'is_correct'    => $isCorrect
        ];
    }

    // 4. Calculate Percentage
    $percentage = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;
    
    // Passing criteria (e.g., 80%)
    $passMark = 80;
    $isPassed = $percentage >= $passMark;

    // 5. Save the Result to the Database
    $result = \App\Models\ExamResult::create([
        'user_id'            => auth()->id(),
        'training_module_id' => $module->id,
        'total_questions_attempted' => $totalQuestions,
        'correct_answers'    => $correctCount,
        'percentage'         => $percentage,
        'is_passed'          => $isPassed,
        // 'details'         => json_encode($details), // Uncomment if you added a 'details' column
    ]);

    // 6. Redirect to the result view
    return redirect()->route('exams.result', $result->id)
                     ->with($isPassed ? 'success' : 'error', $isPassed ? 'Congratulations!' : 'Please try again.');
}


public function showResult($resultId)
{
    // Load the result along with the module name to display to the user
    $result = \App\Models\ExamResult::with('module')->findOrFail($resultId);

    // Security: Ensure users can only see their own results
    if ($result->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'Unauthorized action.');
    }

    return view('exams.result', compact('result'));
}

public function userHistory()
{
    // Using paginate(10) instead of get() for better performance as history grows
    $results = \App\Models\ExamResult::where('user_id', auth()->id())
                ->with('module')
                ->latest() // Shortcut for orderBy('created_at', 'desc')
                ->paginate(10);

    return view('exams.history', compact('results'));
}

public function adminLogs()
{
    // Fetch stats for the top cards
    $stats = [
        'total'  => \App\Models\ExamResult::count(),
        'passed' => \App\Models\ExamResult::where('is_passed', true)->count(),
        'failed' => \App\Models\ExamResult::where('is_passed', false)->count(),
    ];

    $logs = \App\Models\ExamResult::with(['user', 'module'])
                ->latest()
                ->paginate(15);

    return view('admin.exams.logs', compact('logs', 'stats'));
}

public function showExamDetails($resultId)
{
    // Fetch the result with related user and module
    $result = \App\Models\ExamResult::with(['user', 'module'])->findOrFail($resultId);

    // If you saved the answers in a 'details' JSON column, decode it
    // If not, this view will just show the summary
    $details = json_decode($result->details, true) ?? [];

    return view('admin.exams.details', compact('result', 'details'));
}



}
