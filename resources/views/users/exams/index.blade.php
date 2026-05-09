@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                @php
                    $pendingReadingCount = $modules->where('reading_completed', false)->count();
                    $failedAssessmentCount = $modules->filter(fn ($module) => $module->latestResult && !$module->latestResult->is_passed)->count();
                @endphp
                <h3 class="page-title">Available Training Assessments</h3>
                <p class="text-muted">Review the documents before starting your randomized exam.</p>
                @if (session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                @endif
                @if ($pendingReadingCount > 0)
                    <div class="alert alert-warning mt-3">
                        {{ $pendingReadingCount }} training assessment(s) are waiting for required document reading.
                    </div>
                @endif
                @if ($failedAssessmentCount > 0)
                    <div class="alert alert-info mt-3">
                        {{ $failedAssessmentCount }} assessment(s) need re-attempt after document review.
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            @foreach ($modules as $module)
                @php
                    $totalQuestions = $module->documents->sum(
                        fn($document) => (int) ($document->pivot->question_quota ?? 0),
                    );
                    $status = $module->latestResult;
                    $readTracker = $module->readTracker;
                    $readingCompleted = (bool) ($module->reading_completed ?? false);
                @endphp
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 class="card-title text-primary">{{ $module->name }}</h4>
                                @if ($status && $status->is_passed)
                                    <label class="badge badge-success">Passed</label>
                                @elseif($status)
                                    <label class="badge badge-danger">Failed</label>
                                @else
                                    <label class="badge badge-warning text-white">Pending</label>
                                @endif
                            </div>

                            <p class="small text-muted mb-3">
                                <i class="mdi mdi-help-circle-outline"></i> This exam contains
                                <strong>{{ $totalQuestions }}</strong> random questions.
                            </p>

                            <div class="alert {{ $readingCompleted ? 'alert-success' : 'alert-warning' }} py-2 small">
                                @if ($readingCompleted)
                                    Reading completed. Assessment is unlocked.
                                @else
                                    Reading in progress. Required time:
                                    {{ gmdate('i:s', (int) ($readTracker->required_seconds ?? 60)) }}
                                @endif
                            </div>

                            <h6>Study Material:</h6>
                            <ul class="list-unstyled mb-4">
                                @foreach ($module->documents as $doc)
                                    <li class="mb-2">
                                        <i class="mdi mdi-file-pdf text-danger"></i>
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                            class="text-dark small">
                                            {{ $doc->doc_name }} ({{ $doc->doc_number }})
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-auto">
                                @if (!$readingCompleted)
                                    <a href="{{ route('exams.read', $module->id) }}" class="btn btn-warning btn-block shadow">
                                        <i class="mdi mdi-book-open-page-variant"></i> Complete Reading First
                                    </a>
                                @elseif($status && $status->is_passed)
                                    <button class="btn btn-outline-secondary btn-block" disabled>Completed</button>
                                @elseif($status && !$status->is_passed)
                                    <a href="{{ route('exams.take', $module->id) }}"
                                        class="btn btn-danger btn-block shadow">
                                        <i class="mdi mdi-reload"></i> Re-Attempt
                                    </a>
                                @else
                                    <a href="{{ route('exams.take', $module->id) }}"
                                        class="btn btn-primary btn-block shadow">
                                        <i class="mdi mdi-play"></i> Start Assessment
                                    </a>
                                @endif
                                @if ($status)
                                    <p class="text-center mt-2 small">Last Attempt: {{ $status->percentage }}% on
                                        {{ $status->created_at->format('d M') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
            @endforeach
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
