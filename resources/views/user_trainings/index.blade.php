@extends('partials.app')

@section('content')
<div class="content-wrapper">
    @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="mdi mdi-lock-outline"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <span class="font-weight-bold">{{ $programLabel }} — Enrollment & Progress</span>

                <ul class="nav nav-pills">
                    @foreach ($programTabs as $tab)
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3 {{ $tab['is_active'] ? 'active' : '' }}"
                                href="{{ $tab['url'] }}">
                                {{ $tab['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card-body p-0">

            @if(!empty($departmentBreakdown) && count($departmentBreakdown))
            <div class="p-3 border-bottom bg-light">
                <h6 class="mb-3">Department-wise Breakup</h6>

                <div class="row">
                    @foreach($departmentBreakdown as $summary)
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3 h-100 bg-white">
                            <strong>{{ $summary['department'] }}</strong>

                            <div class="small text-muted mt-2">
                                Users: {{ $summary['users'] }}
                            </div>

                            <div class="small text-warning">
                                Pending: {{ $summary['pending'] }}
                            </div>

                            <div class="small text-info">
                                In Progress: {{ $summary['in_progress'] }}
                            </div>

                            <div class="small text-success">
                                Completed: {{ $summary['completed'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="small text-muted">
                    Pending items are shown first in the list below.
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Training Module</th>
                            <th class="text-center">Progress</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($trainees as $user)
                        @foreach($user->assigned_progress as $m)

                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                <br>

                                <small class="text-muted">
                                    {{ $user->user_id }}
                                </small>
                            </td>

                            <td>
                                <span class="font-weight-bold">
                                    {{ $m['name'] }}
                                </span>

                                <br>

                                <span class="badge badge-{{ $m['color'] }} small">
                                    {{ $m['status_label'] ?? ucfirst($m['status']) }}
                                </span>

                                <div class="small text-muted mt-1">
                                    Completion: {{ $m['percent'] }}%
                                </div>
                            </td>

                            <td style="width: 300px; min-width: 300px;">

                                @if(count($m['steps']) > 0)

                                @php
                                $stepCount = count($m['steps']);
                                $width = $stepCount > 0
                                ? (100 / $stepCount) . '%'
                                : '100%';
                                @endphp

                                <div
                                    class="d-flex overflow-hidden rounded mb-2"
                                    style="height: 35px; font-weight: 600; font-size: 14px;">

                                    @foreach($m['steps'] as $step)

                                    @php
                                    $modalId = 'stepModal_' . $user->id . '_' . $step['id'];
                                    @endphp

                                    <div
                                        class="d-flex align-items-center justify-content-center text-white"
                                        style="
                                                            width: {{ $width }};
                                                            background-color: {{ $step['is_completed'] ? '#08a045' : '#f39c12' }};
                                                            cursor: pointer;
                                                            min-height: 35px;
                                                        "
                                                        onclick="openTrainingModal('{{ $modalId }}')"
                                                    >
                                                        {{ $step['short_code'] }}
                                                    </div>

                                                @endforeach
                                            </div>

                                        @endif

                                        <div class="progress mb-1" style="height: 6px;">
                                            <div
                                                class="progress-bar bg-{{ $m['color'] }}"
                                                style="width: {{ $m['percent'] }}%;"
                                            ></div>
                                        </div>

                                        <small class="d-block text-center text-muted">
                                            @if($m['total'] === 0)
                                                No steps configured yet
                                            @else
                                                {{ $m['percent'] }}% ({{ $m['completed'] }}/{{ $m['total'] }})
                                            @endif
                                        </small>

                                    </td>

                                    <td class="text-right">

                                    @if($user->is_locked)

                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm"
                                            disabled
                                            title="{{ $user->locked_reason }}"
                                        >
                                            <i class="mdi mdi-lock-outline"></i> Locked
                                        </button>

                                        <div class="small text-muted mt-1">
                                            {{ $user->locked_reason }}
                                        </div>

                                    @elseif($m['is_completed'])

                                        <a
                                            href="{{ route('user.training.report', [$user->id, $m['id']]) }}"
                                            class="btn btn-primary btn-sm"
                                        >
                                           View Report
                                        </a>

                                    @elseif(! $m['can_log_steps'])

                                        {{-- Assessment not cleared yet, so no step may be logged. --}}
                                        <a href="{{ route('user.training.show', [$user->id, $m['id']]) }}"
                                            class="btn btn-outline-secondary btn-sm"
                                            title="{{ $m['blocked_reason'] }}">
                                            <i class="mdi mdi-lock-outline"></i> Assessment Pending
                                        </a>

                                        <div class="small text-muted mt-1">
                                            {{ $m['blocked_reason'] }}
                                        </div>

                                    @else

                                        <a href="{{ route('user.training.show', [$user->id, $m['id']]) }}"
                                            class="btn btn-primary btn-sm">
                                            Manage Training
                                        </a>
                                    @endif



                                    </td>
                                </tr>

                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No user is currently enrolled in {{ $programLabel }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- ALL MODALS OUTSIDE TABLE --}}

@foreach($trainees as $user)
@foreach($user->assigned_progress as $m)

@if(count($m['steps']) > 0)

@foreach($m['steps'] as $step)

@php
$modalId = 'stepModal_' . $user->id . '_' . $step['id'];
@endphp

<div
    class="modal fade"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $step['name'] }}
                </h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <p>
                    <strong>Completed At:</strong>
                    Completed Date time details
                </p>

                <p>
                    <strong>Short Code:</strong>
                    {{ $step['short_code'] }}
                </p>

                <p>
                    <strong>Status:</strong>

                    @if($step['is_completed'])
                    <span class="badge badge-success">
                        Completed
                    </span>
                    @else
                    <span class="badge badge-warning">
                        Pending
                    </span>
                    @endif
                </p>

            </div>
        </div>
    </div>
</div>

@endforeach

@endif

@endforeach
@endforeach
@endsection

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #999;
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    function openTrainingModal(id) {
        $('#' + id).modal('show');
    }
</script>
@endpush