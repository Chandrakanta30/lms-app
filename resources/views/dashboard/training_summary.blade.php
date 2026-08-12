@extends('partials.app')

@section('title', 'Training Summary')

@section('content')
    <div class="content-wrapper">
        <div class="card shadow-sm">
            <div class="card-body">
                @php
                    $trainingSummaryRows = $trainingSummaries ?? collect();
                @endphp

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h4 class="card-title mb-1">Training Summary</h4>
                        <p class="text-muted mb-0">
                            Refreshment and induction trainings only. Failed includes users who did not attempt the exam before the allowed time ended.
                        </p>
                    </div>

                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                        Back to Dashboard
                    </a>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.08em;">Programs</div>
                            <h4 class="mb-0">{{ $trainingSummaryRows->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.08em;">Register Total</div>
                            <h4 class="mb-0">{{ $trainingSummaryRows->sum('register_count') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.08em;">Ended</div>
                            <h4 class="mb-0">{{ $trainingSummaryRows->where('is_expired', true)->count() }}</h4>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:0.85rem;">
                        <thead style="background:rgba(15,23,42,0.03);">
                            <tr>
                                <th class="px-4 py-3 fw-semibold">Training Name</th>
                                <th class="py-3 fw-semibold">Date</th>
                                <th class="py-3 fw-semibold">Register</th>
                                <th class="py-3 fw-semibold">Present</th>
                                <th class="py-3 fw-semibold">Absent</th>
                                <th class="py-3 fw-semibold">Passed Count</th>
                                <th class="py-3 fw-semibold">Failed Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trainingSummaryRows as $summary)
                                <tr style="border-top:1px solid rgba(15,23,42,0.06);">
                                    <td class="px-4 py-3">
                                        <div class="fw-medium">{{ $summary->name }}</div>
                                        @if($summary->is_expired)
                                            <span class="badge badge-danger mt-1">Ended</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-muted">
                                        {{ $summary->date ? \Carbon\Carbon::parse($summary->date)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3">{{ $summary->register_count }}</td>
                                    <td class="py-3 text-success fw-semibold">{{ $summary->present_count }}</td>
                                    <td class="py-3 text-danger fw-semibold">{{ $summary->absent_count }}</td>
                                    <td class="py-3 text-success fw-semibold">
                                        <a
                                            href="{{ route('sessions.index', ['training_id' => $summary->id, 'status' => 'passed']) }}"
                                            class="text-success text-decoration-none"
                                        >
                                            {{ $summary->passed_count }}
                                        </a>
                                    </td>
                                    <td class="py-3 text-danger fw-semibold">
                                        <a
                                            href="{{ route('sessions.index', ['training_id' => $summary->id, 'status' => 'failed']) }}"
                                            class="text-danger text-decoration-none"
                                        >
                                            {{ $summary->failed_count }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No training summary data available yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
