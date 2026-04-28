@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}

@section('title', 'My Profile')

@section('content')
<div class="content-wrapper">


<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Search Name/Employee ID</label>  
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Enter keyword...">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-group w-100">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    <a href="{{ route('users.index') }}" class="btn btn-light btn-block">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="card-title">User Management</h4>
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-icon-text">
                    <i class="mdi mdi-plus btn-icon-prepend"></i> Add User 
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Employee ID</th>
                            <th>Roles</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->corporate_id }}</td>
                            <td>
                                @foreach($user->getRoleNames() as $role)
                                    <label class="badge badge-info">{{ $role }}</label>
                                @endforeach
                            </td>

                            <td>{{ $user->department->name ?? 'N/A' }}</td>
                            <td>{{ $user->designation->name ?? 'N/A' }}</td>

                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info mr-1" title="View" aria-label="View">
                                    <i class="mdi mdi-eye"></i>
                                </a>

                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-dark mr-1" title="Edit" aria-label="Edit">
                                    <i class="mdi mdi-pencil"></i>
                                </a>

                                <a href="{{ route('user.training.card', $user->id) }}" class="btn btn-sm btn-dark mr-1" title="Show Report" aria-label="Show Report">
                                    <i class="mdi mdi-file-document-outline"></i>
                                </a>


                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')" title="Delete" aria-label="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }} {{-- Pagination links --}}
            </div>
        </div>
    </div>
</div>
@endsection
