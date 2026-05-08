@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin">
            <h3 class="page-title">My Training Modules</h3>
            <p class="text-muted">Select a training module to open its attendance sheet.</p>
        </div>
    </div>

    <div class="row">
        @forelse($modules as $module)
        @php
            $attendanceUrl = route('attendance', $module->id);
        @endphp
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title text-primary mb-2">
                            <a href="{{ $attendanceUrl }}" class="text-primary stretched-link">{{ $module->name }}</a>
                        </h4>
                        <label class="badge badge-warning text-white">Assigned</label>
                    </div>

                    <p class="small text-muted mb-4">
                        <i class="mdi mdi-account-group-outline"></i> View trainee attendance and module details.
                    </p>

                    <div class="mt-auto">
                        <a href="{{ $attendanceUrl }}" class="btn btn-primary btn-block">
                            <i class="mdi mdi-clipboard-text-outline"></i> Mark Attendance
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
