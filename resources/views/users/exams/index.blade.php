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

                        @php
                            $extension = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                        @endphp

                        <li class="mb-2">

                            <a href="javascript:void(0)"
                            onclick="openDocument(
                                '{{ route('documents.view', $doc->id) }}',
                                '{{ $extension }}'
                            )"
                            class="text-dark small text-decoration-none">

                                {{-- Icons --}}
                                @if(in_array($extension, ['pdf']))
                                    <i class="mdi mdi-file-pdf text-danger"></i>

                                @elseif(in_array($extension, ['doc', 'docx']))
                                    <i class="mdi mdi-file-word text-primary"></i>

                                @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <i class="mdi mdi-file-image text-success"></i>

                                @else
                                    <i class="mdi mdi-file"></i>
                                @endif

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



<!-- Bootstrap Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Document Preview</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body p-0 text-center">

                <!-- PDF / DOC iframe -->
                <iframe id="documentFrame"
                        width="100%"
                        height="700px"
                        style="border:none; display:none;">
                </iframe>

                <!-- Image Preview -->
                <img id="imagePreview"
                     src=""
                     class="img-fluid"
                     style="display:none; max-height:700px;">

                <!-- DOCX Preview -->
                <div id="docxPreview"
                     class="text-left p-4 bg-white"
                     style="display:none; max-height:700px; overflow:auto;">
                </div>

            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')
<script src="https://unpkg.com/mammoth@1.8.0/mammoth.browser.min.js"></script>
<script>

async function openDocument(url, extension)
{
    let frame = document.getElementById('documentFrame');
    let image = document.getElementById('imagePreview');
    let docxPreview = document.getElementById('docxPreview');

    frame.style.display = 'none';
    image.style.display = 'none';
    docxPreview.style.display = 'none';
    docxPreview.innerHTML = '';

    // IMAGE FILES
    if (['jpg','jpeg','png','gif','webp'].includes(extension)) {

        image.src = url;
        image.style.display = 'block';
    }

    // DOCX rendered as HTML
    else if (extension === 'docx') {

        try {
            let response = await fetch(url);
            let arrayBuffer = await response.arrayBuffer();
            let result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });

            docxPreview.innerHTML = result.value || '<p class="text-muted mb-0">No preview content available.</p>';
            docxPreview.style.display = 'block';
        } catch (error) {
            docxPreview.innerHTML = '<div class="p-4 text-center text-danger">Unable to preview this DOCX file inline.</div>';
            docxPreview.style.display = 'block';
        }
    }

    // PDF / browser-previewable document files
    else if (['pdf', 'ppt', 'pptx'].includes(extension)) {

        frame.src = url;
        frame.style.display = 'block';
    }

    // OTHER FILES
    else {

        window.open(url, '_blank');
        return;
    }

    let modal = new bootstrap.Modal(
        document.getElementById('documentModal')
    );

    modal.show();
}

// Clear modal on close
document.getElementById('documentModal')
    .addEventListener('hidden.bs.modal', function () {

        document.getElementById('documentFrame').src = '';
        document.getElementById('imagePreview').src = '';
        document.getElementById('docxPreview').innerHTML = '';
    });

</script>

@endpush
