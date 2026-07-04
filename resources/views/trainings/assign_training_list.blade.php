@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin">
            <h3 class="page-title">My Training Modules</h3>
            <p class="text-muted">Select a training module to open its attendance sheet.</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($modules as $module)
        @php
            $attendanceUrl = route('attendance', $module->id);
        @endphp
        <div class="col-md-6 d-flex">
            <div class="card shadow-sm border-0 h-100 w-100">
                <div class="card-body position-relative d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title text-primary mb-0">
                            <a href="{{ $attendanceUrl }}" class="text-primary stretched-link fw-semibold">{{ $module->name }}</a>
                        </h5>
                        <span class="badge bg-label-warning text-warning">Assigned</span>
                    </div>

                    <p class="small text-muted mb-4 flex-grow-1">
                        <i class="icon-base ti tabler-users me-1"></i> View trainee attendance and module details.
                    </p>

                    <div class="mt-auto">
                        <a href="{{ $attendanceUrl }}" class="btn btn-primary w-100">
                            <i class="icon-base ti tabler-clipboard-check me-1"></i> Mark Attendance
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No Modules Found
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
