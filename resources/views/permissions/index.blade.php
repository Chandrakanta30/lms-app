@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}

@section('title', 'My Profile')

@section('content')
<div class="content-wrapper">

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            <div class="d-flex justify-content-between mb-3">
                <h4 class="card-title">Permissions</h4>
                <a href="{{ route('permissions.create') }}" class="btn btn-primary">Add Permission</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Guard</th>
                            <th width="200px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->guard_name }}</td>
                            <td>
                                <a class="btn btn-sm btn-dark" href="{{ route('permissions.edit', $permission->id) }}">Edit</a>
                                <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this permission?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
    </div>
@endsection
