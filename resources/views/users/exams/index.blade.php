@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin">
            <h3 class="page-title">Available Training Assessments</h3>
            <p class="text-muted">Review the documents before starting your randomized exam.</p>
        </div>
    </div>

    <div class="row">
        @foreach($modules as $module)
        @php
            $totalQuestions = $module->documents->sum(fn ($document) => (int) ($document->pivot->question_quota ?? 0));
            $status = $module->latestResult;
        @endphp
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title text-primary">{{ $module->name }}</h4>
                        @if($status && $status->is_passed)
                            <label class="badge badge-success">Passed</label>
                        @elseif($status)
                            <label class="badge badge-danger">Failed</label>
                        @else
                            <label class="badge badge-warning text-white">Pending</label>
                        @endif
                    </div>

                    <p class="small text-muted mb-3">
                        <i class="mdi mdi-help-circle-outline"></i> This exam contains <strong>{{ $totalQuestions }}</strong> random questions.
                    </p>

                    <h6>Study Material:</h6>
                    <ul class="list-unstyled mb-4">
                        @foreach($module->documents as $doc)
                        <li class="mb-2">
                            <i class="mdi mdi-file-pdf text-danger"></i> 
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-dark small">
                                {{ $doc->doc_name }} ({{ $doc->doc_number }})
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto">
                        @if($status && $status->is_passed)
                            <button class="btn btn-outline-secondary btn-block" disabled>Completed</button>
                        @elseif(!$status)
                            <a href="{{ route('exams.take', $module->id) }}" class="btn btn-primary btn-block shadow">
                                <i class="mdi mdi-play"></i> Start Assessment
                            </a>
                        @endif
                        
                        @if($status)
                            <p class="text-center mt-2 small">Last Attempt: {{ $status->percentage }}% on {{ $status->created_at->format('d M') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
