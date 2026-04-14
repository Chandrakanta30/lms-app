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

        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="bg-light">
                    <tr>
                        <th>S.No.</th>
                        <th>Date</th>
                        <th>Trainee Name</th>
                        <th>Register No. & Page No.</th>
                        <th>Topic</th>
                        <th>Name of the Trainer</th>
                        <th>Signature of the Trainer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $index => $session)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($session->training_date)->format('d-m-Y') }}</td>
                        <td class="text-left">{{ $session->trainee->name }}</td>
                        <td>{{ $session->register_no }} / {{ $session->page_no }}</td>
                        <td class="text-left">{{ $session->topic }}</td>
                        <td>{{ $session->trainer->name }}</td>



                        
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
                        <td colspan="7">No register entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
                    <select name="trainer_id" class="form-control" required>
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