@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Assessment Breakdown</h3>
        <a href="{{ route('admin.exams.logs') }}" class="btn btn-light btn-sm">
            <i class="mdi mdi-arrow-left"></i> Back to Logs
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title">Employee Info</h4>
                    <div class="text-center py-3">
                        <div class="bg-soft-primary rounded-circle d-inline-block p-3 mb-2">
                            <i class="mdi mdi-account h2 text-primary"></i>
                        </div>
                        <h5>{{ $result->user->name }}</h5>
                        <p class="text-muted small">{{ $result->user->email }}</p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Module:</span>
                        <span class="font-weight-bold">{{ $result->module->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Date:</span>
                        <span class="text-muted">{{ $result->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <span class="badge {{ $result->is_passed ? 'badge-success' : 'badge-danger' }}">
                            {{ $result->is_passed ? 'PASSED' : 'FAILED' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card bg-primary text-white shadow">
                <div class="card-body text-center">
                    <h1 class="display-4 font-weight-bold mb-0">{{ round($result->percentage) }}%</h1>
                    <p>Final Score</p>
                    <small>{{ $result->correct_answers }} correct out of {{ $result->total_questions_attempted }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Question Breakdown</h4>
                    
                    @if(!empty($details))
                        @foreach($details as $index => $item)
                        <div class="border rounded p-3 mb-3 {{ $item['is_correct'] ? 'border-left-success' : 'border-left-danger' }}" style="border-left-width: 5px !important;">
                            <div class="d-flex justify-content-between">
                                <h6 class="font-weight-bold">Question {{ $index + 1 }}</h6>
                                @if($item['is_correct'])
                                    <span class="text-success small"><i class="mdi mdi-check-circle"></i> Correct</span>
                                @else
                                    <span class="text-danger small"><i class="mdi mdi-close-circle"></i> Incorrect</span>
                                @endif
                            </div>
                            <p class="mb-2">{{ $item['question_text'] }}</p>
                            <div class="row small">
                                <div class="col-6">
                                    <span class="text-muted">User Answer:</span> 
                                    <span class="font-weight-bold {{ $item['is_correct'] ? 'text-success' : 'text-danger' }}">
                                        {{ $item['user_answer'] ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Correct Answer:</span> 
                                    <span class="font-weight-bold text-dark">{{ $item['actual_answer'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-alert-circle-outline text-muted h1"></i>
                            <p class="text-muted">Detailed question breakdown was not recorded for this attempt.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-success { border-left: 5px solid #28a745 !important; }
    .border-left-danger { border-left: 5px solid #dc3545 !important; }
    .bg-soft-primary { background-color: rgba(75, 73, 172, 0.1); }
</style>
@endsection