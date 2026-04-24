@extends('partials.app')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

.attendance-wrapper {
    max-width: 1000px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 25px;
}

.header h2 {
    font-weight: 600;
    color: #111827;
}

.subtitle {
    font-size: 14px;
    color: #6b7280;
    max-width: 650px;
    margin: 8px auto 0;
    line-height: 1.6;
}

.trainer-name {
    text-align: center;
    font-size: 15px;
    margin-bottom: 25px;
    color: #6b7280;
}

/* Table */
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

thead th {
    text-align: left;
    font-size: 13px;
    color: #6b7280;
    padding: 12px;
    text-transform: uppercase;
}

tbody tr {
    background: #f9fafb;
    transition: 0.2s;
}

tbody tr:hover {
    background: #eef2ff;
}

td {
    padding: 14px 12px;
    font-size: 14px;
}

/* Toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 22px;
    margin-right: 10px;
    vertical-align: middle;
}

.switch input {
    opacity: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    background: #ccc;
    border-radius: 34px;
    top: 0; left: 0; right: 0; bottom: 0;
    transition: .3s;
}

.slider:before {
    content: "";
    position: absolute;
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: .3s;
}

.switch input:checked + .slider {
    background: #22c55e;
}

.switch input:checked + .slider:before {
    transform: translateX(20px);
}

/* Status */
.status {
    font-weight: 600;
    color: #ef4444;
}

.status.present {
    color: #22c55e;
}

/* Pagination FIX */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 25px;
}

.pagination {
    display: flex;
    gap: 6px;
    list-style: none;
    padding: 0;
}

.pagination li a,
.pagination li span {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    font-size: 13px;
    color: #374151;
}

.pagination .active span {
    background: #22c55e;
    color: #fff;
    border-color: #22c55e;
}
</style>

<div class="content-wrapper">

    <div class="attendance-wrapper">

        <!-- Header -->
        <div class="header">
            <h2>Training Attendance Dashboard</h2>
            <p class="subtitle">
                Mark and manage daily attendance for trainees assigned to this module.
            </p>
        </div>

        <div class="trainer-name">
            Trainer: John Doe
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Trainee</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $users->firstItem() + $loop->index }}</td>

                    <td>
                        <label class="switch">
                            <input 
                                type="checkbox" 
                                onchange="updateStatus(this)"
                                data-id="{{ $user->id }}"
                                {{ $user->is_present ? 'checked' : '' }}
                            >
                            <span class="slider"></span>
                        </label>

                        <span>{{ $user->name }}</span>
                    </td>

                    <td class="status {{ $user->is_present ? 'present' : '' }}">
                        {{ $user->is_present ? 'Present' : 'Absent' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding:15px;">
                        No users found
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>

    </div>

</div>

<script>
function updateStatus(toggle) {
    const row = toggle.closest("tr");
    const status = row.querySelector(".status");

    if (toggle.checked) {
        status.innerText = "Present";
        status.classList.add("present");
    } else {
        status.innerText = "Absent";
        status.classList.remove("present");
    }

    // NOTE: You should also send AJAX to backend here (recommended)
}
</script>

@endsection