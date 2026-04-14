@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}
@section('content')
<div class="content-wrapper">

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Create New Role</h4>
                    <p class="card-description"> Define a role and assign specific permissions </p>
                    
                    <form class="forms-sample" action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        
                        {{-- Role Name Input --}}
                        <div class="form-group">
                            <label for="roleName">Role Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   id="roleName" placeholder="e.g. Manager, Editor" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        {{-- Permission Selection Grid --}}
                        <div class="form-group">
                            <label class="mb-3"><strong>Assign Permissions to this Role:</strong></label>
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check form-check-flat form-check-primary">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input">
                                                {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                            <i class="input-helper"></i></label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="form-group">
                            <label>Job Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe the responsibilities of this role...">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Required Training / Certifications</label>
                            <textarea name="training_required" class="form-control" rows="2" placeholder="e.g. Safety Level 1, GDPR Training...">{{ old('training_required') }}</textarea>
                        </div>


                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary mr-2">Create Role</button>
                            <a href="{{ route('roles.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection