@extends('partials.app')

@section('content')
<div class="container mt-5">

    <div class="mb-4">
        <h3 class="page-title">My Training Modules</h3>
        <p class="subtitle">Select a module to view attendance and training details.</p>
    </div>

    <div class="row g-3">

        @forelse($modules as $module)
        <div class="col-md-6 col-lg-4">

            <a href="{{ route('attendance', $module->id) }}" class="text-decoration-none">

                <div class="card module-card p-3">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="module-title">{{ $module->name }}</span>
                        <span class="badge bg-secondary">Active</span>
                    </div>

                    <p class="text-muted small mb-0">
                        Click to view attendance sheet and trainee details.
                    </p>

                </div>

            </a>

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