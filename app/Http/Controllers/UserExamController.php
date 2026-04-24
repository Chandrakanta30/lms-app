<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
