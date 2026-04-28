@extends('partials.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                <div>
                    <h4 class="card-title mb-1">{{ isset($user) ? 'Edit User' : 'Create New User' }}</h4>
                    <p class="text-muted mb-0">Organize identity, access, and profile details in a shorter, easier-to-scan layout.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-light">Back to users</a>
            </div>

            <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $user->name ?? '') }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employee ID</label>
                                <input
                                    type="text"
                                    name="corporate_id"
                                    class="form-control"
                                    value="{{ old('corporate_id', $user->corporate_id ?? '') }}"
                                    required
                                >
                        </div>
                    </div>


                    <div class="col-md-6">
                            <div class="form-group">
                            <label>Internal ID</label>
                                <input
                                    type="text"
                                    name="internal_id"
                                    class="form-control"
                                    value="{{ old('internal_id', $user->internal_id ?? '') }}"
                                >
                            </div>
                    </div>


                    <!--make changes in email field -->

                 




                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Job Role</label>
                            <select name="roles[]" class="form-control js-example-basic-multiple" multiple="multiple" style="width:100%">
                                @foreach($roles as $role)
                                    <option
                                        value="{{ $role->name }}"
                                        {{ (isset($user) && $user->hasRole($role->name)) ? 'selected' : '' }}
                                    >
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password {{ isset($user) ? '(Leave blank to keep current)' : '' }}</label>
                            <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                        </div>
                    </div>

                    @if(!isset($user))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option
                                        value="{{ $dept->id }}"
                                        {{ (isset($user) && $user->department_id == $dept->id) ? 'selected' : '' }}
                                    >
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Designation</label>
                            <select name="designation_id" class="form-control">
                                <option value="">Select Designation</option>
                                @foreach($designations as $desg)
                                    <option
                                        value="{{ $desg->id }}"
                                        {{ (isset($user) && $user->designation_id == $desg->id) ? 'selected' : '' }}
                                    >
                                        {{ $desg->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Years of Experience</label>
                            <input
                                type="number"
                                name="experience_years"
                                class="form-control"
                                value="{{ old('experience_years', $user->experience_years ?? 0) }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Highest Qualification</label>
                            <input
                                type="text"
                                name="qualification"
                                class="form-control"
                                value="{{ old('qualification', $user->qualification ?? '') }}"
                                placeholder="e.g. M.Sc. Chemistry"
                            >
                        </div>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-group w-100 mb-md-4">
                            <div class="form-check form-check-flat form-check-primary p-3 rounded" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.08);">
                                <label class="form-check-label">
                                    <input
                                        type="checkbox"
                                        name="is_trainer"
                                        value="1"
                                        class="form-check-input"
                                        {{ (isset($user) && $user->is_trainer) ? 'checked' : '' }}
                                    >
                                    Authorize as a Trainer
                                    <i class="input-helper"></i>
                                </label>
                                <div class="mt-2">
                                    <small class="text-muted">If checked, this user will appear in the interacted person list during training logs.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success mr-2">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
                    <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
