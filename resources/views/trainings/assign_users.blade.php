@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-3">
        <div class="card shadow">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Enroll Trainees: {{ $module->name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('trainings.save-users', $module->id) }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">Only checked users will be enrolled upon saving.</small>
                        </div>
                    </div>

                    <div style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-sm table-hover border" id="userTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th width="50px">Enroll</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Personal Start Date</th>
                                    <th>Personal Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allUsers as $index => $user)
                                @php 
                                    $enrolled = $module->trainees->where('id', $user->id)->first();
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="users[{{ $index }}][enrolled]" value="1" 
                                            class="user-checkbox" {{ $enrolled ? 'checked' : '' }}>
                                        <input type="hidden" name="users[{{ $index }}][user_id]" value="{{ $user->id }}">
                                    </td>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td>{{ $user->department->name }}</td>
                                    <td>
                                        <input type="date" name="users[{{ $index }}][start_date]" 
                                            value="{{ $enrolled ? $enrolled->pivot->start_date : $module->start_date }}" 
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="date" name="users[{{ $index }}][end_date]" 
                                            value="{{ $enrolled ? $enrolled->pivot->end_date : $module->end_date }}" 
                                            class="form-control form-control-sm">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success px-5">Save Enrollment & Dates</button>
                        <a href="{{ route('trainings.index') }}" class="btn btn-link text-muted">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple search filter
    document.getElementById('userSearch').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#userTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>
@endsection