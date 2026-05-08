@extends('partials.app')

@section('title', $document->doc_name)

@section('content')
<div class="content-wrapper document-show-page">
    <div class="document-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start">
                <div>
                    <div class="d-flex flex-wrap align-items-center mb-3">
                        <span class="badge badge-light border px-3 py-2 mr-2">{{ $document->doc_type ?? 'Document' }}</span>
                        <span class="badge badge-dark px-3 py-2">{{ $document->doc_number }}</span>
                    </div>
                    <h2 class="mb-2">{{ $document->doc_name }}</h2>
                    <p class="text-muted mb-0">
                        Version {{ $document->version ?? '1.0' }} •
                        Uploaded {{ $document->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>

                <div class="mt-4 mt-lg-0 d-flex flex-wrap">
                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-primary mr-2 mb-2">
                        <i class="mdi mdi-open-in-new"></i> Open File
                    </a>
                    <form action="{{ route('master-documents.review', $document->id) }}" method="POST" class="mr-2 mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-check-decagram"></i>
                            {{ $document->reviewed_by ? 'Submit Review Again' : 'Submit Review' }}
                        </button>
                    </form>
                    <a href="{{ route('master-documents.index') }}" class="btn btn-light mb-2">
                        <i class="mdi mdi-arrow-left"></i> Back to Master Documents
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Document Overview</h4>

                    <div class="info-grid">
                        <div class="info-card">
                            <span class="info-label">Uploaded By</span>
                            <strong>{{ $document->uploader->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="info-card">
                            <span class="info-label">Reviewed By</span>
                            <strong>{{ $document->reviewer->name ?? 'Pending review' }}</strong>
                        </div>
                        <div class="info-card">
                            <span class="info-label">Reviewed At</span>
                            <strong>{{ $document->reviewed_at ? $document->reviewed_at->format('d M Y, h:i A') : 'Not reviewed yet' }}</strong>
                        </div>
                        <div class="info-card">
                            <span class="info-label">Question Pool</span>
                            <strong>{{ $document->questions_count }} questions</strong>
                        </div>
                        <div class="info-card">
                            <span class="info-label">Linked Trainings</span>
                            <strong>{{ $document->modules->count() }}</strong>
                        </div>
                        <div class="info-card">
                            <span class="info-label">Current Reviewer ID</span>
                            <strong>{{ $document->reviewed_by ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    <div class="review-panel mt-4">
                        <h6 class="mb-2">Review Action</h6>
                        <p class="text-muted mb-3">
                            When you submit a review, the system stores the logged-in user's ID automatically in the reviewer field.
                        </p>
                        <form action="{{ route('master-documents.review', $document->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="mdi mdi-account-check-outline"></i>
                                {{ $document->reviewed_by ? 'Update Review Owner' : 'Mark as Reviewed' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Document Preview</h4>
                            <p class="text-muted mb-0">View the source file and review linked learning usage.</p>
                        </div>
                        <a href="{{ route('master-questions.index', $document->id) }}" class="btn btn-outline-dark mt-3 mt-md-0">
                            <i class="mdi mdi-format-list-bulleted"></i> Manage Question Pool
                        </a>
                    </div>

                    @if($isPreviewable)
                        <div class="preview-frame">
                            <iframe src="{{ asset('storage/' . $document->file_path) }}" title="{{ $document->doc_name }}"></iframe>
                        </div>
                    @else
                        <div class="empty-preview">
                            <i class="mdi mdi-file-outline"></i>
                            <h5>Inline preview is not available for this file type</h5>
                            <p class="text-muted mb-3">Open the file in a new tab to review the complete document.</p>
                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-primary">
                                Open Document
                            </a>
                        </div>
                    @endif

                    <div class="linked-training-block mt-4">
                        <h5 class="mb-3">Linked Training Programs</h5>

                        @if($document->modules->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Training Name</th>
                                            <th>Question Quota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($document->modules as $module)
                                            <tr>
                                                <td>{{ $module->name }}</td>
                                                <td>{{ $module->pivot->question_quota ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-preview compact">
                                <i class="mdi mdi-link-off"></i>
                                <p class="mb-0">This document is not linked to any training program yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .document-show-page {
        --doc-ink: #17324d;
        --doc-muted: #5f7387;
        --doc-border: #dde6ef;
        --doc-surface: #ffffff;
        --doc-accent: #0f766e;
    }

    .document-hero {
        background:
            radial-gradient(circle at top right, rgba(29, 78, 216, 0.12), transparent 28%),
            linear-gradient(135deg, #f8fbff, #edf8f1);
        border: 1px solid rgba(15, 118, 110, 0.08);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .info-card {
        border: 1px solid var(--doc-border);
        border-radius: 18px;
        padding: 1rem;
        background: #fbfdff;
    }

    .info-label {
        display: block;
        color: var(--doc-muted);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.4rem;
    }

    .review-panel {
        border: 1px dashed var(--doc-border);
        border-radius: 18px;
        padding: 1.25rem;
        background: linear-gradient(180deg, #fcfffd, #f2faf6);
    }

    .preview-frame {
        min-height: 720px;
        border: 1px solid var(--doc-border);
        border-radius: 22px;
        overflow: hidden;
        background: #eef3f8;
    }

    .preview-frame iframe {
        width: 100%;
        min-height: 720px;
        border: 0;
        background: #fff;
    }

    .empty-preview {
        border: 1px dashed var(--doc-border);
        border-radius: 22px;
        padding: 3rem 1.5rem;
        text-align: center;
        background: #fbfdff;
    }

    .empty-preview.compact {
        padding: 1.5rem;
    }

    .empty-preview i {
        font-size: 2rem;
        color: var(--doc-accent);
        margin-bottom: 0.75rem;
    }

    @media (max-width: 767.98px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .preview-frame,
        .preview-frame iframe {
            min-height: 480px;
        }
    }
</style>
@endsection
