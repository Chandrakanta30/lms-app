<?php

namespace App\Http\Controllers;

use App\Models\DocumentReadTracker;
use App\Models\TrainingModule;
use App\Models\TrainingSessions;
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

    public function showReadingRoom($moduleId)
    {
        $module = TrainingModule::with('examDocuments')->findOrFail($moduleId);

        if ($module->examDocuments->isEmpty()) {
            return redirect()->route('exam.list')
                ->with('error', 'No reviewed documents are enabled for this assessment yet.');
        }

        if ($module->training_type === 'classroom' && !$this->hasClassroomAttendance($module)) {
            return redirect()->route('exam.list')
                ->with('error', 'Your attendance for this classroom training has not been marked by the trainer yet. You cannot access the documents until then.');
        }

        $requiredSeconds = $module->examRequiredReadingSeconds();

        $tracker = DocumentReadTracker::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'training_module_id' => $module->id,
            ],
            [
                'required_seconds' => $requiredSeconds,
                'started_at' => now(),
            ]
        );

        if (!$tracker->started_at) {
            $tracker->started_at = now();
        }

        $tracker->required_seconds = max((int) $tracker->required_seconds, $requiredSeconds);
        $tracker->save();

        $elapsedSeconds = $tracker->started_at ? $tracker->started_at->diffInSeconds(now()) : 0;
        $remainingSeconds = max(0, (int) $tracker->required_seconds - $elapsedSeconds);

        return view('exams.read_documents', compact('module', 'tracker', 'remainingSeconds'));
    }

    public function completeReading($moduleId)
    {
        $module = TrainingModule::with('examDocuments')->findOrFail($moduleId);

        if ($module->examDocuments->isEmpty()) {
            return redirect()->route('exam.list')
                ->with('error', 'No reviewed documents are enabled for this assessment yet.');
        }

        $tracker = DocumentReadTracker::where('user_id', auth()->id())
            ->where('training_module_id', $module->id)
            ->firstOrFail();

        $requiredSeconds = max($module->examRequiredReadingSeconds(), (int) $tracker->required_seconds);
        $elapsedSeconds = $tracker->started_at ? $tracker->started_at->diffInSeconds(now()) : 0;

        if ($elapsedSeconds < $requiredSeconds) {
            return back()->with('error', 'Please complete the required reading time before starting the assessment.');
        }

        $tracker->update([
            'required_seconds' => $requiredSeconds,
            'completed_at' => now(),
        ]);

        if ($module->training_type === 'self_training') {
            $module->trainees()->updateExistingPivot(auth()->id(), [
                'attendance_status' => 'present',
                'attendance_marked_at' => now(),
                'attendance_marked_by' => auth()->id(),
            ]);
        }

        return redirect()->route('exam.list')->with('success', 'Reading completed. Assessment is now enabled.');
    }

    public function takeExam($moduleId)
    {
        $module = TrainingModule::with('examDocuments')->findOrFail($moduleId);

        $tracker = DocumentReadTracker::where('user_id', auth()->id())
            ->where('training_module_id', $module->id)
            ->first();

        if (!$tracker || !$tracker->completed_at) {
            return redirect()->route('exams.read', $module->id)
                ->with('error', 'Please complete the required document reading before starting the assessment.');
        }

        $examPaper = collect();

        foreach ($module->examDocuments as $doc) {
            $quota = (int) ($doc->pivot->question_quota ?? 0);
            if ($quota <= 0) {
                continue;
            }

            // Pick random questions from the Master Pool of this document
            $randomQuestions = \App\Models\MasterQuestion::where('master_document_id', $doc->id)
                ->inRandomOrder()
                ->limit($quota)
                ->get();

            $examPaper = $examPaper->concat($randomQuestions);
        }

        // Shuffle final list so questions from different SOPs are mixed
        $examPaper = $examPaper->shuffle();

        if ($examPaper->isEmpty()) {
            return redirect()->route('exam.list')
                ->with('error', 'No reviewed documents with questions are currently enabled for this assessment.');
        }

        session([
            $this->examPaperSessionKey($module->id) => $examPaper->pluck('id')->values()->all(),
        ]);


        return view('exams.take', compact('module', 'examPaper'));
    }



    public function submitExam(Request $request, $moduleId)
    {
        // 1. Validate that answers were actually sent
        $request->validate([
            'answers' => 'required|array',
        ]);


        $module = TrainingModule::with('examDocuments')->findOrFail($moduleId);
        $tracker = DocumentReadTracker::where('user_id', auth()->id())
            ->where('training_module_id', $module->id)
            ->first();

        if (!$tracker || !$tracker->completed_at) {
            return redirect()->route('exams.read', $module->id)
                ->with('error', 'Please complete the required document reading before submitting the assessment.');
        }

        $storedQuestionIds = session()->pull($this->examPaperSessionKey($module->id), []);
        $storedQuestionIds = array_values(array_filter(array_map('intval', (array) $storedQuestionIds)));

        $userAnswers = $request->input('answers'); // Format: [question_id => "Yes/No"]
        $questionIds = !empty($storedQuestionIds) ? $storedQuestionIds : array_map('intval', array_keys($userAnswers));

        // 2. Fetch only the questions that were in the user's exam paper
        // This ensures we grade against the correct pool
        $questions = \App\Models\MasterQuestion::whereIn('id', $questionIds)->get();

        $totalQuestions = !empty($storedQuestionIds) ? count($storedQuestionIds) : $questions->count();

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
                'user_answer' => $submittedAnswer,
                'actual_answer' => $question->correct_answer,
                'is_correct' => $isCorrect
            ];
        }

        // 4. Calculate Percentage
        $percentage = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

        // Passing criteria (e.g., 80%)
        $passMark = 60;
        $isPassed = $percentage >= $passMark;

        // 5. Save the Result to the Database
        $result = \App\Models\ExamResult::create([
            'user_id' => auth()->id(),
            'training_module_id' => $module->id,
            'total_questions_attempted' => $totalQuestions,
            'correct_answers' => $correctCount,
            'percentage' => $percentage,
            'is_passed' => $isPassed,
            'details' => $details,
        ]);


        // 6. Log a Training Card entry for self-training passes (no trainer to log attendance for them)
        if ($module->training_type === 'self_training' && $isPassed) {
            $trainerId = optional($module->trainers()->first())->id ?? $module->created_by ?? auth()->id();

            TrainingSessions::updateOrCreate(
                [
                    'trainee_id' => auth()->id(),
                    'topic' => $module->name,
                ],
                [
                    'training_date' => now()->toDateString(),
                    'trainer_id' => $trainerId,
                    'topic' => $module->name,
                    'register_no' => 'N/A',
                    'page_no' => 'N/A',
                    'session_brief_type' => 'Self Training',
                    'session_comments' => 'Auto-logged on self-training exam pass.',
                    'is_approved' => true,
                    'approved_by' => $trainerId,
                    'approved_at' => now(),
                ]
            );
        }

        // 7. Redirect to the result view
        return redirect()->route('exams.result', $result->id)
            ->with($isPassed ? 'success' : 'error', $isPassed ? 'Congratulations!' : 'Please try again.');
    }

    private function hasClassroomAttendance(TrainingModule $module): bool
    {
        $trainee = $module->trainees()->where('users.id', auth()->id())->first();

        return $trainee && $trainee->pivot->attendance_status === 'present';
    }

    private function examPaperSessionKey(int $moduleId): string
    {
        return 'exam_paper.' . auth()->id() . '.' . $moduleId;
    }


    public function showResult($resultId)
    {
        // Load the result along with the module name to display to the user
        $result = \App\Models\ExamResult::with(['user', 'module'])->findOrFail($resultId);

        if (!$this->canViewExamResult($result)) {
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
        if (!$this->canViewAllExamResults()) {
            abort(403, 'Unauthorized action.');
        }

        // Fetch stats for the top cards
        $stats = [
            'total' => \App\Models\ExamResult::count(),
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

        if (!$this->canViewExamResult($result)) {
            abort(403, 'Unauthorized action.');
        }

        $details = is_array($result->details)
            ? $result->details
            : (json_decode($result->details, true) ?? []);

        $canViewAllExamResults = $this->canViewAllExamResults();

        return view('admin.exams.details', compact('result', 'details', 'canViewAllExamResults'));
    }

    private function canViewExamResult(\App\Models\ExamResult $result): bool
    {
        return $result->user_id === auth()->id() || $this->canViewAllExamResults();
    }

    private function canViewAllExamResults(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        try {
            if (method_exists($user, 'getRoleNames')) {
                $adminRoles = ['admin', 'super admin', 'super-admin'];
                $hasAdminRole = $user->getRoleNames()
                    ->map(fn($role) => strtolower($role))
                    ->intersect($adminRoles)
                    ->isNotEmpty();

                if ($hasAdminRole) {
                    return true;
                }
            }

            if (method_exists($user, 'getAllPermissions')) {
                return $user->getAllPermissions()
                    ->pluck('name')
                    ->intersect(['admin-logs', 'exam-logs', 'exams'])
                    ->isNotEmpty();
            }
        } catch (\Throwable $exception) {
            return false;
        }

        return false;
    }

}
