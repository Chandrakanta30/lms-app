@extends('partials.app')

@section('title', 'Training Management')

@php
$statusMap = [
    'created' => ['label' => 'Created', 'class' => 'badge-dark'],
    'inreview' => ['label' => 'In Review', 'class' => 'badge-warning'],
    'reviewed' => ['label' => 'Reviewed', 'class' => 'badge-info'],
    'approved' => ['label' => 'Approved', 'class' => 'badge-success'],
];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="card shadow-sm">
        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Training Management</h4>
                    <p class="text-muted mb-0">
                        Track workflow status, review assignments, and open a complete detail view
                        for each training program.
                    </p>
                </div>

                <a href="{{ route('trainings.create') }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-plus"></i> Add New Program
                </a>
            </div>

            <div class="accordion custom-accordion" id="trainingAccordion">

                @foreach ($trainings as $training)

                    @php
                        $statusMeta = $statusMap[$training->status ?? 'created'] ?? $statusMap['created'];
                    @endphp

                    <!-- CARD START -->
                    <div class="card border mb-3">

                        <!-- HEADER -->
                        <div class="card-header bg-white py-3">

                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                                <!-- LEFT -->
                                <div class="training-left">

                                    <button
                                        class="btn btn-link text-decoration-none text-dark font-weight-bold p-0 training-title"
                                        data-toggle="collapse"
                                        data-target="#collapse{{ $training->id }}"
                                        aria-expanded="false">

                                        <i class="mdi mdi-chevron-down-circle-outline mr-2 text-primary"></i>

                                        {{ $training->name }}
                                    </button>

                                    <div class="mt-2 d-flex flex-wrap align-items-center gap-2">

                                        <span class="badge badge-outline-secondary">
                                            {{ $training->steps->count() }} Steps
                                        </span>

                                        <span class="badge {{ $statusMeta['class'] }}">
                                            {{ $statusMeta['label'] }}
                                        </span>

                                    </div>
                                </div>

                                <!-- RIGHT -->
                                <div class="action-buttons">

                                    <a href="{{ route('trainings.show', $training->id) }}"
                                        class="btn btn-sm btn-outline-dark"
                                        title="View Training">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>

                                    <a href="{{ route('admin.modules.linkDocs', $training->id) }}"
                                        class="btn btn-sm btn-outline-info"
                                        title="Link Documents">
                                        <i class="mdi mdi-link-variant"></i>
                                    </a>

                                    <a href="{{ route('manage-trainers', $training->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Trainers & Venue">

                                        <i class="mdi mdi-account-group"></i>

                                        <span class="badge badge-primary ml-1">
                                            {{ $training->trainers->count() }}
                                        </span>
                                    </a>

                                    <a href="{{ route('manage-users', $training->id) }}"
                                        class="btn btn-sm btn-outline-success"
                                        title="Users">

                                        <i class="mdi mdi-account-tie"></i>

                                        <span class="badge badge-success ml-1">
                                            {{ $training->trainees->count() }}
                                        </span>
                                    </a>

                                    <!-- STATUS -->
                                    <form action="{{ route('trainings.toggle-status', $training->id) }}"
                                        method="POST">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="status-toggle-btn {{ $training->is_active ? 'active' : 'inactive' }}">

                                            <span class="status-indicator"></span>

                                            <span class="status-text">
                                                {{ $training->is_active ? 'Active' : 'Inactive' }}
                                            </span>

                                        </button>
                                    </form>

                                    <!-- EDIT -->
                                    <a href="{{ route('trainings.edit', $training->id) }}"
                                        class="btn btn-sm btn-light text-info">

                                        <i class="mdi mdi-pencil"></i>
                                    </a>

                                    <!-- DELETE -->
                                    <form action="{{ route('trainings.destroy', $training->id) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-light text-danger"
                                            onclick="return confirm('Delete Program?')">

                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                    </form>

                                </div>

                            </div>

                        </div>

                        <!-- COLLAPSE BODY -->
                        <div id="collapse{{ $training->id }}"
                            class="collapse"
                            data-parent="#trainingAccordion">

                            <div class="card-body bg-light">

                                <!-- INFO -->
                                <div class="row mb-3">

                                    <div class="col-md-3 mb-2">
                                        <strong>Status:</strong>
                                        {{ $statusMeta['label'] }}
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <strong>Type:</strong>
                                        {{ ucwords(str_replace('_', ' ', $training->training_type ?? 'classroom')) }}
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <strong>Start:</strong>

                                        {{ $training->start_date
                                            ? \Illuminate\Support\Carbon::parse($training->start_date)->format('d M Y')
                                            : 'N/A' }}
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <strong>End:</strong>

                                        {{ $training->end_date
                                            ? \Illuminate\Support\Carbon::parse($training->end_date)->format('d M Y')
                                            : 'N/A' }}
                                    </div>

                                </div>

                                <!-- TABS -->
                                <ul class="nav nav-pills mb-3"
                                    id="pills-tab-{{ $training->id }}"
                                    role="tablist">

                                    <li class="nav-item">
                                        <a class="nav-link active py-1 px-3"
                                            data-toggle="pill"
                                            href="#steps-{{ $training->id }}">
                                            Steps
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3"
                                            data-toggle="pill"
                                            href="#trainers-{{ $training->id }}">
                                            Trainers
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3"
                                            data-toggle="pill"
                                            href="#users-{{ $training->id }}">
                                            Trainees
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3"
                                            data-toggle="pill"
                                            href="#docs-{{ $training->id }}">
                                            Documents
                                        </a>
                                    </li>

                                </ul>

                                <!-- TAB CONTENT -->
                                <div class="tab-content bg-white p-3 border rounded shadow-sm">

                                    <!-- STEPS -->
                                    <div class="tab-pane fade show active"
                                        id="steps-{{ $training->id }}">

                                        <div class="list-group list-group-flush">

                                            @foreach ($training->steps as $step)

                                                <div class="list-group-item d-flex justify-content-between align-items-center">

                                                    <div>
                                                        <b class="text-primary mr-2">
                                                            {{ $loop->iteration }}
                                                        </b>

                                                        {{ $step->name }}
                                                    </div>

                                                    <span class="text-muted small">
                                                        {{ ucfirst($training->status ?? 'created') }}
                                                    </span>

                                                </div>

                                            @endforeach

                                        </div>

                                    </div>

                                    <!-- TRAINERS -->
                                    <div class="tab-pane fade"
                                        id="trainers-{{ $training->id }}">

                                        @forelse($training->trainers as $trainer)

                                            <div class="d-inline-block border rounded p-2 m-1 bg-light">

                                                <i class="mdi mdi-account-star text-primary"></i>

                                                {{ $trainer->name }}

                                                <div class="text-muted small">
                                                    {{ $trainer->designation->name ?? 'No designation' }}
                                                </div>

                                            </div>

                                        @empty

                                            <p class="text-muted small">
                                                No trainers assigned.
                                            </p>

                                        @endforelse

                                    </div>

                                    <!-- USERS -->
                                    <div class="tab-pane fade"
                                        id="users-{{ $training->id }}">

                                        <div class="row">

                                            @forelse($training->trainees as $user)

                                                <div class="col-md-4 mb-2">

                                                    <div class="small p-3 rounded bg-light border">

                                                        <strong>{{ $user->name }}</strong><br>

                                                        <span class="text-muted">
                                                            {{ $user->email }}
                                                        </span><br>

                                                        <span class="text-muted">
                                                            {{ $user->designation->name ?? 'No designation' }}
                                                        </span>

                                                    </div>

                                                </div>

                                            @empty

                                                <div class="col-12 text-muted small">
                                                    No users enrolled yet.
                                                </div>

                                            @endforelse

                                        </div>

                                    </div>

                                    <!-- DOCUMENTS -->
                                    <div class="tab-pane fade"
                                        id="docs-{{ $training->id }}">

                                        <table class="table table-sm">

                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Document Name</th>
                                                    <th>Question Quota</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @forelse($training->documents as $doc)

                                                    <tr>

                                                        <td>{{ $doc->doc_name }}</td>

                                                        <td>
                                                            <span class="badge badge-dark">
                                                                {{ $doc->pivot->question_quota }}
                                                            </span>
                                                        </td>

                                                    </tr>

                                                @empty

                                                    <tr>
                                                        <td colspan="2" class="text-muted">
                                                            No documents linked.
                                                        </td>
                                                    </tr>

                                                @endforelse

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                    <!-- CARD END -->

                @endforeach

            </div>

        </div>
    </div>
</div>

<style>

    .custom-accordion .card-header {
        border-bottom: 0;
        transition: background 0.3s ease;
    }

    .custom-accordion .card-header:hover {
        background-color: #f8f9fa !important;
    }

    .training-title {
        font-size: 16px;
    }

    .nav-pills .nav-link {
        font-size: 0.85rem;
        color: #6c757d;
        border: 1px solid transparent;
    }

    .nav-pills .nav-link.active {
        background-color: #4b49ac !important;
        color: #fff;
    }

    .badge-outline-secondary {
        border: 1px solid #6c757d;
        color: #6c757d;
        background: transparent;
    }

    .status-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 12px;
        border: none;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s ease;
        height: 32px;
    }

    .status-toggle-btn.active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }

    .status-toggle-btn.inactive {
        background: rgba(148, 163, 184, 0.15);
        color: #94a3b8;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-toggle-btn:hover {
        transform: scale(1.03);
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .action-buttons .btn,
    .action-buttons form button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 10px;
        font-size: 12px;
        border-radius: 6px;
        white-space: nowrap;
    }

    .action-buttons .btn i {
        margin-right: 3px;
    }

    .action-buttons form {
        margin: 0;
    }

    .gap-2 {
        gap: 8px;
    }

    .gap-3 {
        gap: 12px;
    }

    @media (max-width: 991px) {

        .action-buttons {
            width: 100%;
            margin-top: 10px;
        }

    }

</style>

@endsection