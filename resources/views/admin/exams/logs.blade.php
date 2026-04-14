@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title text-dark">Employee Assessment Logs</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Exam Logs</li>
            </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-primary border-0 text-white shadow">
                <div class="card-body">
                    <h6 class="font-weight-normal">Total Attempts</h6>
                    <h2 class="mb-0">{{ $stats['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-success border-0 text-white shadow">
                <div class="card-body">
                    <h6 class="font-weight-normal">Total Passes</h6>
                    <h2 class="mb-0">{{ $stats['passed'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-danger border-0 text-white shadow">
                <div class="card-body">
                    <h6 class="font-weight-normal">Total Failures</h6>
                    <h2 class="mb-0">{{ $stats['failed'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="card-title">Detailed Audit Trail</h4>
                {{-- Add search/filter here later if needed --}}
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="bg-light text-dark font-weight-bold">
                        <tr>
                            <th>Employee</th>
                            <th>Module Name</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Status</th>
                            <th>Date & Time</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded-circle mr-3">
                                        <i class="mdi mdi-account text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="font-weight-bold">{{ $log->user->name ?? 'Unknown' }}</span><br>
                                        <small class="text-muted">{{ $log->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $log->module->name ?? 'Deleted Module' }}</td>
                            <td class="text-center">
                                <div class="badge badge-outline-dark font-weight-bold">
                                    {{ round($log->percentage) }}% ({{ $log->correct_answers }}/{{ $log->total_questions_attempted }})
                                </div>
                            </td>
                            <td class="text-center">
                                @if($log->is_passed)
                                    <span class="text-success font-weight-bold">
                                        <i class="mdi mdi-check-decagram mr-1"></i>PASSED
                                    </span>
                                @else
                                    <span class="text-danger font-weight-bold">
                                        <i class="mdi mdi-alert-circle mr-1"></i>FAILED
                                    </span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $log->created_at->format('d M Y') }}<br>
                                <span class="text-muted">{{ $log->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.exams.details', $log->id) }}" class="btn btn-sm btn-inverse-info">
                                    View Breakdown
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No assessment logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary { background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%); }
    .bg-gradient-success { background: linear-gradient(to right, #43e97b 0%, #38f9d7 100%); }
    .bg-gradient-danger { background: linear-gradient(to right, #fa709a 0%, #fee140 100%); }
    .btn-inverse-info { background-color: rgba(30, 172, 190, 0.1); color: #1eacbe; border: none; }
    .btn-inverse-info:hover { background-color: #1eacbe; color: white; }
</style>
@endsection