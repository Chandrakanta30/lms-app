@extends('partials.app')

@section('title', isset($permission) ? 'Edit Permission' : 'Create Permission')

@section('content')
<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">{{ isset($permission) ? 'Edit Permission' : 'Create Permission' }}</h4>
                            <p class="text-muted mb-0">Use concise, system-friendly names like `user-create` or `training-list`.</p>
                        </div>
                        <a href="{{ route('permissions.index') }}" class="btn btn-light">Back</a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ isset($permission) ? route('permissions.update', $permission->id) : route('permissions.store') }}" method="POST">
                        @csrf
                        @if(isset($permission))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="name">Permission Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                placeholder="e.g. user-create"
                                value="{{ old('name', $permission->name ?? '') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="guard_name">Guard Name</label>
                            <select name="guard_name" id="guard_name" class="form-control" required>
                                @php $selectedGuard = old('guard_name', $permission->guard_name ?? 'web'); @endphp
                                <option value="web" {{ $selectedGuard === 'web' ? 'selected' : '' }}>Web</option>
                                <option value="api" {{ $selectedGuard === 'api' ? 'selected' : '' }}>API</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary mr-2">
                                {{ isset($permission) ? 'Update Permission' : 'Create Permission' }}
                            </button>
                            <a href="{{ route('permissions.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
