@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid pt-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manage Trainers: {{ $module->name }}</h4>
                    <a href="{{ route('trainings.index') }}" class="btn btn-sm btn-light">Back to List</a>
                </div>
                <div class="card-body">
                    @php
                        $trainerRequired = ($module->training_type ?? 'classroom') !== 'self_training';
                    @endphp

                    @if(!$trainerRequired)
                        <div class="alert alert-info">
                            Trainer assignment is optional for self training programs.
                        </div>
                    @endif

                    <form action="{{ route('trainings.save-trainers', $module->id) }}" method="POST">
                        @csrf
                        <table class="table table-bordered" id="trainer-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Select Trainer</th>
                                    <th>Assignment Start Date</th>
                                    <th>Assignment End Date</th>
                                    <th width="50px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($module->trainers as $index => $trainer)
                                    <tr>
                                        <td>
                                            <select name="trainers[{{ $index }}][user_id]" class="form-control"
                                                {{ $trainerRequired ? 'required' : '' }}>
                                                @foreach ($allUsers as $user)
                                                    <option value="{{ $user->id }}"
                                                        {{ $user->id == $trainer->id ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ $user->department->name ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="date" name="trainers[{{ $index }}][start_date]"
                                                value="{{ $trainer->pivot->start_date }}" class="form-control" {{ $trainerRequired ? 'required' : '' }}>
                                        </td>
                                        <td>
                                            <input type="date" name="trainers[{{ $index }}][end_date]"
                                                value="{{ $trainer->pivot->end_date }}" class="form-control" {{ $trainerRequired ? 'required' : '' }}>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="this.closest('tr').remove()">
                                                <i class="fas fa-times"></i> x
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="empty-row">
                                        <td colspan="4" class="text-center text-muted">No trainers assigned. Click "Add
                                            Trainer" to start.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            <button type="button" class="btn btn-info btn-sm" onclick="addTrainerRow()">
                                <i class="fas fa-plus"></i> + Add Trainer
                            </button>
                        </div>

                        <hr class="mt-4">

                        <h5 class="mb-3">Assign Venues</h5>

                        <table class="table table-bordered" id="venue-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Select Venue</th>
                                    <th width="50px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($module->venues ?? [] as $index => $venue)
                                    <tr>
                                        <td>
                                            <select name="venues[{{ $index }}][venue_id]" class="form-control"
                                                required>
                                                @foreach ($allVenues as $v)
                                                    <option value="{{ $v->venue_id }}"
                                                        {{ $v->venue_id == $venue->venue_id ? 'selected' : '' }}>
                                                        {{ $v->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="this.closest('tr').remove()">x</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="venue-empty-row">
                                        <td colspan="2" class="text-center text-muted">
                                            No venues assigned. Click "Add Venue"
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <button type="button" class="btn btn-warning btn-sm mt-2" onclick="addVenueRow()">
                            + Add Venue
                        </button>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-success px-4">Update Assignments</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>

    <script>
        let trainerCount = {{ $module->trainers->count() }};

        function addTrainerRow() {
            // Remove empty message if it exists
            const emptyRow = document.querySelector('.empty-row');
            if (emptyRow) emptyRow.remove();

            const tbody = document.querySelector('#trainer-table tbody');
            const row = document.createElement('tr');

            row.innerHTML = `
            <td>
                <select name="trainers[${trainerCount}][user_id]" class="form-control" {{ $trainerRequired ? 'required' : '' }}>
                    <option value="">-- Choose User --</option>
                    @foreach ($allUsers as $user)
<option value="{{ $user->id }}">
    {{ $user->name }} ({{ $user->department->name ?? 'N/A' }})
</option>
                    @endforeach
                </select>
            </td>
            <td><input type="date" name="trainers[${trainerCount}][start_date]" class="form-control" {{ $trainerRequired ? 'required' : '' }}></td>
            <td><input type="date" name="trainers[${trainerCount}][end_date]" class="form-control" {{ $trainerRequired ? 'required' : '' }}></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">x</button></td>
        `;

            tbody.appendChild(row);
            trainerCount++;
        }


        let venueCount = {{ isset($module->venues) ? $module->venues->count() : 0 }};

        function addVenueRow() {
            const emptyRow = document.querySelector('.venue-empty-row');
            if (emptyRow) emptyRow.remove();

            const tbody = document.querySelector('#venue-table tbody');
            const row = document.createElement('tr');

            row.innerHTML = `
        <td>
            <select name="venues[${venueCount}][venue_id]" class="form-control" required>
                <option value="">-- Choose Venue --</option>
                @foreach ($allVenues as $v)
                    <option value="{{ $v->venue_id }}">{{ $v->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm"
                onclick="this.closest('tr').remove()">x</button>
        </td>
    `;

            tbody.appendChild(row);
            venueCount++;
        }
    </script>
@endsection
