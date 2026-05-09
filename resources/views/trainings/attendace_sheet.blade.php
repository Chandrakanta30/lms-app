@extends('partials.app')

@section('content')
<style>
.attendance-toggle-btn {
    width: 46px;
    height: 24px;
    border: 1px solid #ced4da;
    border-radius: 999px;
    background-color: #e9ecef;
    position: relative;
    padding: 0;
    transition: background-color .2s ease, border-color .2s ease;
}

.attendance-toggle-btn .toggle-knob {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .2);
    transition: transform .2s ease;
}

.attendance-toggle-btn.is-on {
    background-color: #28a745;
    border-color: #28a745;
}

.attendance-toggle-btn.is-on .toggle-knob {
    transform: translateX(22px);
}
</style>
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="card-title mb-1">Attendance Sheet</h4>
                    <p class="text-muted mb-0">Module: {{ $module->name }}</p>
                </div>
                <a href="{{ route('training-list') }}" class="btn btn-light btn-sm">Back</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-lg-4 mb-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <h6 class="mb-2">Trainer Acknowledgement</h6>
                        <div class="text-muted small">
                            {{ $attendanceSignerName ? 'Last submitted by ' . $attendanceSignerName : 'No acknowledgement submitted yet.' }}
                        </div>
                        @if($attendanceSignedAt)
                            <div class="small mt-2">{{ \Illuminate\Support\Carbon::parse($attendanceSignedAt)->format('d M Y, h:i A') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-8 mb-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <h6 class="mb-2">Tagged SOPs / Documents</h6>
                        @forelse($module->documents as $document)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <div>
                                    <strong>{{ $document->doc_name }}</strong>
                                    <div class="small text-muted">{{ $document->doc_number }} • {{ $document->doc_type }}</div>
                                </div>
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Open
                                </a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No SOPs or documents tagged to this training yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <form id="attendanceForm" method="POST" action="{{ route('attendance.submit', ['id' => $module->id, 'page' => request('page')]) }}">
                @csrf

                <div class="border rounded p-3 mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Briefed Session</label>
                                <select name="session_brief_type" class="form-control" required>
                                    <option value="">Select brief type</option>
                                    <option value="SOP">Briefly explained about SOP</option>
                                    <option value="STP">Briefly explained about STP</option>
                                    <option value="Protocol">Briefly explained about Protocol</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Duration</label>
                                <input type="text" id="sessionDuration" class="form-control bg-light" placeholder="Auto-calculated" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Comments</label>
                                <textarea name="session_comments" class="form-control" rows="2" placeholder="Add trainer comments or what was covered in the classroom session."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th width="70">S.No.</th>
                                <th>Trainee Name</th>
                                <th>Corporate Id</th>
                                <th>Email</th>
                                <th width="180">Attendance</th>
                                <th width="180">Current Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    $isPresent = ($user->pivot->attendance_status ?? '') === 'present';
                                @endphp
                                <tr data-user-id="{{ $user->id }}">
                                    <td>{{ $users->firstItem() + $loop->index }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->corporate_id ?? '-' }}</td>
                                    <td>{{ $user->email ?? '-' }}</td>
                                    <td>
                                        <input type="hidden" name="listed_user_ids[]" value="{{ $user->id }}">
                                        <input type="hidden" name="attendance[{{ $user->id }}]" value="{{ $isPresent ? '1' : '0' }}" class="attendance-input">
                                        <button
                                            type="button"
                                            class="attendance-toggle-btn {{ $isPresent ? 'is-on' : '' }}"
                                            data-state="{{ $isPresent ? '1' : '0' }}"
                                            aria-label="Toggle attendance for {{ $user->name }}"
                                        >
                                            <span class="toggle-knob"></span>
                                        </button>
                                    </td>
                                    
                                    <td>
                                        <span class="badge status-badge {{ $isPresent ? 'badge-success' : 'badge-danger' }}">
                                            {{ $isPresent ? 'Present' : 'Absent' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No trainees found for this module.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="persistedAttendanceInputs"></div>

                @if($users->count())
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Submit Attendance
                        </button>
                    </div>
                @endif
            </form>

            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<script>
var moduleId = '{{ $module->id }}';
var storageKey = 'attendance_module_' + moduleId;
var attendanceMap = {};

try {
    attendanceMap = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
} catch (e) {
    attendanceMap = {};
}

function setRowState(row, state) {
    var button = row.querySelector('.attendance-toggle-btn');
    var badge = row.querySelector('.status-badge');
    var hiddenInput = row.querySelector('.attendance-input');
    if (!button || !badge || !hiddenInput) return;

    button.dataset.state = state;
    hiddenInput.value = state;

    if (state === '1') {
        button.classList.add('is-on');
        badge.textContent = 'Present';
        badge.classList.remove('badge-danger');
        badge.classList.add('badge-success');
    } else {
        button.classList.remove('is-on');
        badge.textContent = 'Absent';
        badge.classList.remove('badge-success');
        badge.classList.add('badge-danger');
    }
}

function saveAttendanceMap() {
    sessionStorage.setItem(storageKey, JSON.stringify(attendanceMap));
}

document.querySelectorAll('tr[data-user-id]').forEach(function (row) {
    var userId = row.dataset.userId;
    var currentState = row.querySelector('.attendance-input').value;

    if (Object.prototype.hasOwnProperty.call(attendanceMap, userId)) {
        setRowState(row, attendanceMap[userId]);
    } else {
        attendanceMap[userId] = currentState;
    }
});
saveAttendanceMap();

document.querySelectorAll('.attendance-toggle-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        var row = this.closest('tr[data-user-id]');
        if (!row) return;

        var userId = row.dataset.userId;
        var nextState = this.dataset.state === '1' ? '0' : '1';

        setRowState(row, nextState);
        attendanceMap[userId] = nextState;
        saveAttendanceMap();
    });
});

document.getElementById('attendanceForm').addEventListener('submit', function () {
    var container = document.getElementById('persistedAttendanceInputs');
    container.innerHTML = '';

    Object.keys(attendanceMap).forEach(function (userId) {
        var listedInput = document.createElement('input');
        listedInput.type = 'hidden';
        listedInput.name = 'listed_user_ids[]';
        listedInput.value = userId;
        container.appendChild(listedInput);

        var attendanceInput = document.createElement('input');
        attendanceInput.type = 'hidden';
        attendanceInput.name = 'attendance[' + userId + ']';
        attendanceInput.value = attendanceMap[userId];
        container.appendChild(attendanceInput);
    });
});

@if(session('success'))
sessionStorage.removeItem(storageKey);
@endif

function updateDuration() {
    var start = document.querySelector('input[name="start_time"]').value;
    var end = document.querySelector('input[name="end_time"]').value;
    var durationField = document.getElementById('sessionDuration');

    if (!start || !end) {
        durationField.value = '';
        return;
    }

    var startMinutes = parseInt(start.split(':')[0], 10) * 60 + parseInt(start.split(':')[1], 10);
    var endMinutes = parseInt(end.split(':')[0], 10) * 60 + parseInt(end.split(':')[1], 10);

    if (endMinutes <= startMinutes) {
        durationField.value = 'Invalid timing';
        return;
    }

    var totalMinutes = endMinutes - startMinutes;
    var hours = Math.floor(totalMinutes / 60);
    var minutes = totalMinutes % 60;
    durationField.value = hours + 'h ' + minutes + 'm';
}

document.querySelector('input[name="start_time"]').addEventListener('change', updateDuration);
document.querySelector('input[name="end_time"]').addEventListener('change', updateDuration);
</script>
@endsection
