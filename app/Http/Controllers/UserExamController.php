<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentReadTracker;
use App\Models\TrainingModule;
use App\Models\ExamResult;

class UserExamController extends Controller
{
    public function index()
    {
        // Get only "Self Training" modules and include the user's latest attempt
        $today = now()->format('Y-m-d');

        $modules = TrainingModule::with(['documents', 'latestResult' => function($q) {
            // Specify the table name here to avoid ambiguity
            $q->where('exam_results.user_id', auth()->id()); 
        }])
        // ->has('documents')
        ->whereHas('trainees', function($q) {
            // Specify the pivot table name here
            $q->where('training_user.user_id', auth()->id());
        })
        ->where('is_active', true)
        ->get();

        $trackerMap = DocumentReadTracker::where('user_id', auth()->id())
            ->whereIn('training_module_id', $modules->pluck('id'))
            ->get()
            ->keyBy('training_module_id');

        $modules->each(function ($module) use ($trackerMap) {
            $requiredSeconds = max(60, $module->documents->count() * 60);
            $tracker = $trackerMap->get($module->id);

            if (!$tracker) {
                $tracker = new DocumentReadTracker([
                    'training_module_id' => $module->id,
                    'required_seconds' => $requiredSeconds,
                ]);
            } else {
                $tracker->required_seconds = max((int) $tracker->required_seconds, $requiredSeconds);
            }

            $module->setRelation('readTracker', $tracker);
            $module->reading_completed = !is_null($tracker->completed_at);
        });
    
        return view('users.exams.index', compact('modules'));
    }


    public function adminLogs()
    {
        $logs = ExamResult::with(['user', 'module'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.exams.logs', compact('logs'));
    }
}
