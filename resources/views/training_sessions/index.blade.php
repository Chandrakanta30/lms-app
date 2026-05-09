@extends('partials.app')
@section('content')

<div class="content-wrapper">
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title">Training Register Log</h4>
            <button class="btn btn-primary btn-sm" 
        data-toggle="modal" data-target="#logSessionModal"
        data-bs-toggle="modal" data-bs-target="#logSessionModal">
    + Add New Entry
</button>
        </div>

        <form method="GET" action="{{ route('sessions.index') }}" class="border rounded p-3 mb-4 bg-light">
            <div class="row">
                <div class="col-md-3">
                    <label>Trainee</label>
                    <select name="trainee_id" class="form-control">
                        <option value="">All Users</option>
                        @foreach($trainees as $t)
                            <option value="{{ $t->id }}" {{ (string) request('trainee_id') === (string) $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Training / Topic</label>
                    <input type="text" name="topic" class="form-control" value="{{ request('topic') }}" placeholder="Search SOP or topic">
                </div>
                <div class="col-md-2">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="w-100">
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                        <a href="{{ route('sessions.index') }}" class="btn btn-light btn-block">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="bg-light">
                    <tr>
                        <th>S.No.</th>
                        <th>Date</th>
                        <th>Trainee Name</th>
                        <th>Topic</th>
                        <th>Session Brief</th>
                        <th>Timing</th>
                        <th>Name of the Trainer</th>
                        <th>Trainer Acknowledgement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $index => $session)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($session->training_date)->format('d-m-Y') }}</td>
                        <td class="text-left">{{ $session->trainee->name }}</td>
                        <td class="text-left">{{ $session->topic }}</td>
                        <td>
                            <div>{{ $session->session_brief_type ?? 'N/A' }}</div>
                            @if($session->session_comments)
                                <small class="text-muted">{{ $session->session_comments }}</small>
                            @endif
                        </td>
                        <td>
                            @if($session->start_time && $session->end_time)
                                {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} -
                                {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $session->trainer->name ?? 'N/A' }}</td>



                        
                        <td class="align-middle">
                        @if($session->is_approved)
                            <div class="d-flex flex-column align-items-center">
                                <div class="signature-box p-1" style="border: 1px dashed #28a745; background: #f0fff4; border-radius: 4px; min-width: 120px;">
                                    <i class="fas fa-certificate text-success mb-1" title="Verified Signature"></i>
                                    <div class="signature-text" style="font-family: 'Dancing Script', cursive; font-size: 1.2rem; color: #003366;">
                                        {{ $session->approver->name }}
                                    </div>
                                </div>
                                <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                    Digitally Approved<br>
                                    {{ $session->approved_at }}
                                </small>
                            </div>
                        @else
                            @if(auth()->id() == $session->trainer_id || auth()->user()->is_admin)
                                <form action="{{ route('sessions.approve', $session->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm">
                                        <i class="fas fa-signature mr-1"></i> Sign & Approve
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-warning p-2">
                                    <i class="fas fa-clock mr-1"></i> Awaiting Trainer
                                </span>
                            @endif
                        @endif
                    </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">No register entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $sessions->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>


<div class="modal fade" id="logSessionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
        <form action="{{ route('sessions.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Log Training Session</h5></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Date of Training</label>
                    <input type="date" name="training_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Trainee</label>
                    <select name="trainee_id" class="form-control select2" required>
                        @foreach($trainees as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->department->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Trainer (Leader)</label>
                    <select name="trainer_id" class="form-control">
                        <option value="">Optional for self training</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label>Register No.</label>
                        <input type="text" name="register_no" class="form-control" placeholder="e.g. R-01" required>
                    </div>
                    <div class="col-6">
                        <label>Page No.</label>
                        <input type="text" name="page_no" class="form-control" placeholder="e.g. 45" required>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label>Topic</label>
                    <textarea name="topic" class="form-control" rows="2" placeholder="e.g. Safety SOP Training" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update Register</button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection
