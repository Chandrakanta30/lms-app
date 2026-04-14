@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}
@section('content')
<div class="content-wrapper">

<form action="{{ route('roles.update', $role->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="form-group">
        <label>Role Name</label>
        <input type="text" name="name" value="{{ $role->name }}" class="form-control">
    </div>

    <div class="row">
        @foreach($permissions as $permission)
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        {{ $permission->name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    <button type="submit" class="btn btn-success">Update Role</button>
</form>
</div>
@endsection