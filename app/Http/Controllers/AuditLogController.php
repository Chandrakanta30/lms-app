<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->action) {
            $query->where('description', $request->action);
        }

        $logs = $query->paginate(20);

        return view('audit_logs.index', compact('logs'));
    }

    // ✅ THIS METHOD MUST EXIST
    public function moduleLogs($id)
    {
        $logs = Activity::with('causer')
            ->where('subject_type', 'App\Models\TrainingModule')
            ->where('subject_id', $id)
            ->latest()
            ->get();

        return view('audit_logs.module_logs', compact('logs'));
    }
}