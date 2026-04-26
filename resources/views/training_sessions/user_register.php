@extends('partials.app')

@section('content')

<div class="content-wrapper">

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <!-- HEADER -->
            <div class="mb-4">
                <h4 class="card-title mb-1">Trainer Attendance Dashboard</h4>
                <p class="text-muted mb-0">
                    Manage attendance for all your assigned training modules
                </p>
            </div>

            <hr>

            @forelse($modules as $module)

                <!-- MODULE HEADER -->
                <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                    <h5 class="text-primary mb-0">
                        {{ $module->name }}
                    </h5>
                </div>

                <!-- TABLE -->
                <div class="table-responsive mb-4">

                    <table class="table table-bordered text-center">

                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Trainee Name</th>
                                <th>Status</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($module->users as $index => $user)

                                <tr>

                                    <td>{{ $index + 1 }}</td>

                                    <td class="text-left">
                                        {{ $user->name }}
                                    </td>

                                    <td>
                                        <span id="status-{{ $module->id }}-{{ $user->id }}" 
                                              class="badge badge-danger">
                                            Absent
                                        </span>
                                    </td>

                                    <td>
                                        <label class="switch">
                                            <input type="checkbox"
                                                onchange="toggleAttendance(this, '{{ $module->id }}-{{ $user->id }}')">
                                            <span class="slider"></span>
                                        </label>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4" class="text-muted">
                                        No trainees found in this module
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            @empty

                <div class="alert alert-info text-center">
                    No training modules assigned to you
                </div>

            @endforelse

        </div>
    </div>

</div>

<!-- STYLE -->
<style>
.switch {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 22px;
}

.switch input {
    opacity: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    background: #ccc;
    top: 0; left: 0; right: 0; bottom: 0;
    border-radius: 34px;
    transition: .3s;
}

.slider:before {
    content: "";
    position: absolute;
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: .3s;
}

.switch input:checked + .slider {
    background: #28a745;
}

.switch input:checked + .slider:before {
    transform: translateX(20px);
}
</style>

<!-- SCRIPT -->
<script>
function toggleAttendance(el, id) {
    let status = document.getElementById('status-' + id);

    if (el.checked) {
        status.innerText = "Present";
        status.classList.remove("badge-danger");
        status.classList.add("badge-success");
    } else {
        status.innerText = "Absent";
        status.classList.remove("badge-success");
        status.classList.add("badge-danger");
    }
}
</script>

@endsection