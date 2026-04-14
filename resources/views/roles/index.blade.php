@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}
@section('content')
<div class="content-wrapper">

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Roles & Permissions</h4>
            <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">Add Role</a>
            
            <table class="table table-bordered" id="order-listing">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Training Required</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                    <td>
    <strong>{{ $role->name }}</strong><br>
    <small class="text-muted">{{ Str::limit($role->description, 50) }}</small>
</td>
<td>
    <span class="text-info"><i class="mdi mdi-school"></i> {{ $role->training_required ?? 'None' }}</span>
</td>
                        <td>
                            @foreach($role->permissions as $p)
                                <span class="badge badge-info">{{ $p->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-dark">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection