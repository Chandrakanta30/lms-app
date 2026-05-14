@extends('partials.app')

@section('content')

<div class="content-wrapper">

    {{-- Header Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin">

            @php
            $pendingReadingCount = $modules->where('reading_completed', false)->count();

            $failedAssessmentCount = $modules
            ->filter(fn ($module) => $module->latestResult && !$module->latestResult->is_passed)
            ->count();
            @endphp

            <h3 class="page-title">
                Available Training Assessments
            </h3>

            <p class="text-muted">
                Review the documents before starting your randomized exam.
            </p>

            @if (session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
            @endif

            @if ($pendingReadingCount > 0)
            <div class="alert alert-warning mt-3">
                {{ $pendingReadingCount }}
                training assessment(s) are waiting for required document reading.
            </div>
            @endif

            @if ($failedAssessmentCount > 0)
            <div class="alert alert-info mt-3">
                {{ $failedAssessmentCount }}
                assessment(s) need re-attempt after document review.
            </div>
            @endif

        </div>
    </div>

    {{-- Training Cards --}}
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

        <div class="col-md-6 col-lg-4 grid-margin stretch-card">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body d-flex flex-column">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">

                        <h4 class="card-title text-primary mb-0">
                            {{ $module->name }}
                        </h4>

                        @if ($status && $status->is_passed)

                        <span class="badge badge-success">
                            Passed
                        </span>

                        @elseif($status)

                        <span class="badge badge-danger">
                            Failed
                        </span>

                        @else

                        <span class="badge badge-warning text-white">
                            Pending
                        </span>

                        @endif

                    </div>

                    {{-- Question Info --}}
                    <p class="small text-muted mb-3">
                        <i class="mdi mdi-help-circle-outline"></i>

                        Total Questions:
                        <strong>{{ $totalQuestions }}</strong>
                    </p>

                    {{-- Reading Status --}}
                    <div class="alert {{ $readingCompleted ? 'alert-success' : 'alert-warning' }} py-2 small">

                        @if ($readingCompleted)

                        Reading completed. Assessment unlocked.

                        @else

                        Reading required:
                        {{ gmdate('i:s', (int) ($readTracker->required_seconds ?? 60)) }}

                        @endif

                    </div>

                    {{-- Study Materials --}}
                    <h6 class="mt-3">
                        Study Material
                    </h6>

                    <ul class="list-unstyled mb-4">

                        @foreach ($module->documents as $doc)

                        <li class="mb-2">

                            <i class="mdi mdi-file-pdf text-danger"></i>

                            <a href="{{ asset('storage/' . $doc->file_path) }}"
                                target="_blank"
                                class="text-dark small">

                                {{ $doc->doc_name }}
                                ({{ $doc->doc_number }})

                            </a>

                        </li>

                        @endforeach

                    </ul>

                    {{-- Action Buttons --}}
                    <div class="mt-auto">

                        @if (!$readingCompleted)

                        <a href="{{ route('exams.read', $module->id) }}"
                            class="btn btn-warning btn-block">

                            Complete Reading

                        </a>

                        @elseif($status && $status->is_passed)

                        <button class="btn btn-success btn-block" disabled>

                            Completed

                        </button>

                        @elseif($status && !$status->is_passed)

                        <a href="{{ route('exams.take', $module->id) }}"
                            class="btn btn-danger btn-block">

                            Re-Attempt

                        </a>

                        @else

                        <a href="{{ route('exams.take', $module->id) }}"
                            class="btn btn-primary btn-block">

                            Start Assessment

                        </a>

                        @endif

                        @if ($status)

                        <p class="text-center mt-2 small">

                            Last Attempt:
                            {{ $status->percentage }}%
                            on
                            {{ $status->created_at->format('d M Y') }}

                        </p>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

@endsection