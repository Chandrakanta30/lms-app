@extends('partials.app')
@section('content')

    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="row mb-4 border border-dark align-items-center">
                    <div class="col-3 text-center py-3 border-right border-dark">
                        <div style="font-size: 20px;">SMS</div>
                        <div style="font-size: 16px;">Central Lab</div>
                    </div>

                    <div class="col-6 text-center py-3">
                        <h4 class="mb-0">STAFF TRAINING LOG BOOK</h4>
                    </div>

                    <div class="col-3 text-center py-2 border-left border-dark">
                        <img src="{{ asset('assets/images/sms-logo.jpg') }}" alt="SMS Logo"
                            style="width: 70px; height: 70px; object-fit: contain;">
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#logSessionModal"
                        data-bs-toggle="modal" data-bs-target="#logSessionModal">
                        + Add New Entry
                    </button>
                </div>

                <form method="GET" action="{{ route('sessions.index') }}" class="border rounded p-3 mb-4 bg-light">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Trainee</label>
                            <select name="trainee_id" class="form-control">
                                <option value="">All Users</option>
                                @foreach ($trainees as $t)
                                    <option value="{{ $t->id }}"
                                        {{ (string) request('trainee_id') === (string) $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Training / Topic</label>
                            <input type="text" name="topic" class="form-control"
                                value="{{ request('topic') ?: $selectedTraining->name ?? '' }}"
                                placeholder="Search SOP or topic">
                        </div>
                        <div class="col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="passed" {{ $selectedStatus === 'passed' ? 'selected' : '' }}>Passed</option>
                                <option value="failed" {{ $selectedStatus === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
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
                                <th>Name of the Trainee</th>
                                <th>Training Module</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Name of the Trainer</th>
                                <th>Status</th>
                                <th>Signature of the Trainer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $index => $assignment)
                                <tr>
                                    <td>{{ $assignments->firstItem() + $index }}</td>
                                    <td class="text-left">{{ $assignment->user->name ?? 'N/A' }}</td>
                                    <td class="text-left">{{ $assignment->module->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('d-m-Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->format('d-m-Y') : 'N/A' }}
                                    </td>
                                    <td>{{ $assignment->trainer_name ?? 'N/A' }}</td>

                                    <td>
                                        @php
                                            $reassignmentNote = $assignment->reassignment_note ?? null;
                                            $isExpiredTraining =
                                                (bool) (optional($assignment->module)->isExpired() ?? false);
                                        @endphp

                                        @if (($assignment->status ?? 'pending') === 'failed')
                                            @php
                                                $deadlineOpen = $assignment->can_reassign ?? false;
                                            @endphp

                                            @if ($deadlineOpen)
                                                <button type="button" class="training-status-action js-open-reassign-modal"
                                                    data-toggle="modal" data-target="#reassignTrainingModal"
                                                    data-bs-toggle="modal" data-bs-target="#reassignTrainingModal"
                                                    data-assignment-id="{{ $assignment->id }}"
                                                    data-trainee-id="{{ $assignment->user_id }}"
                                                    data-training-id="{{ $assignment->module->id ?? '' }}"
                                                    data-training-name="{{ $assignment->module->name ?? 'N/A' }}"
                                                    data-training-expired="{{ $isExpiredTraining ? '1' : '0' }}"
                                                    data-reassignment-note="{{ $reassignmentNote }}">
                                                    <span class="default-label">Failed</span>
                                                    <span class="hover-label">Click to Re-Assign</span>
                                                </button>
                                            @else
                                                <span class="training-status-action disabled">
                                                    <span class="default-label">Failed</span>
                                                    <span class="hover-label">Re-assign unavailable</span>
                                                </span>
                                            @endif
                                        @else
                                            <span
                                                class="badge {{ $assignment->status_class ?? 'badge-warning' }} p-2 text-uppercase">
                                                {{ $assignment->status_label ?? 'Pending' }}
                                            </span>
                                        @endif

                                        @if ($reassignmentNote)
                                            <small class="d-block mt-1 text-muted">
                                                {{ $reassignmentNote }}
                                            </small>
                                        @endif
                                    </td>

                                    <td class="align-middle">
                                        @if ($assignment->is_self_training ?? false)
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="signature-box p-1"
                                                    style="border: 1px dashed #28a745; background: #f0fff4; border-radius: 4px; min-width: 120px;">
                                                    <i class="fas fa-certificate text-success mb-1"
                                                        title="Self Signature"></i>
                                                    <div class="signature-text"
                                                        style="font-family: 'Dancing Script', cursive; font-size: 1.2rem; color: #003366;">
                                                        {{ $assignment->user->name ?? 'N/A' }}
                                                    </div>
                                                </div>
                                                <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                                    Self Training<br>
                                                    {{ optional($assignment->latest_exam_result)->created_at ? \Carbon\Carbon::parse(optional($assignment->latest_exam_result)->created_at)->format('d M Y') : '' }}
                                                </small>
                                            </div>
                                        @elseif (optional($assignment->signature_session)->is_approved)
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="signature-box p-1"
                                                    style="border: 1px dashed #28a745; background: #f0fff4; border-radius: 4px; min-width: 120px;">
                                                    <i class="fas fa-certificate text-success mb-1"
                                                        title="Verified Signature"></i>
                                                    <div class="signature-text"
                                                        style="font-family: 'Dancing Script', cursive; font-size: 1.2rem; color: #003366;">
                                                        {{ optional(optional($assignment->signature_session)->approver)->name ?? 'N/A' }}
                                                    </div>
                                                </div>
                                                <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                                    Digitally Approved<br>
                                                    {{ optional($assignment->signature_session)->approved_at }}
                                                </small>
                                            </div>
                                        @else
                                            @php
                                                $canSignAndApprove = $assignment->can_sign_and_approve ?? false;
                                                $currentUser = auth()->user();
                                                $isPrivilegedApprover =
                                                    $currentUser &&
                                                    $currentUser->hasRole([
                                                        'Admin',
                                                        'Super Admin',
                                                        'admin',
                                                        'super admin',
                                                        'super-admin',
                                                        'Coordinator',
                                                        'coordinator',
                                                        'Co-ordinator',
                                                        'co-ordinator',
                                                    ]);
                                                $canShowApproval =
                                                    $assignment->signature_session &&
                                                    (auth()->id() == $assignment->signature_session->trainer_id ||
                                                        $isPrivilegedApprover);
                                            @endphp

                                            @if ($canShowApproval)
                                                <form
                                                    action="{{ route('sessions.approve', $assignment->signature_session->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm"
                                                        {{ !$canSignAndApprove ? 'disabled' : '' }}>
                                                        <i class="fas fa-signature mr-1"></i> Sign & Approve
                                                    </button>
                                                </form>
                                                @if (!$canSignAndApprove)
                                                    <small class="text-danger d-block mt-2">
                                                        Disabled until the trainee passes the exam.
                                                    </small>
                                                @endif
                                            @else
                                                @if ($isPrivilegedApprover && $canSignAndApprove)
                                                    <span class="badge badge-warning p-2">
                                                        <i class="fas fa-clock mr-1"></i> Session not found
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning p-2">
                                                        <i class="fas fa-clock mr-1"></i> Awaiting Trainer
                                                    </span>
                                                @endif
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
                    {{ $assignments->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>


        <div class="modal fade" id="logSessionModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('sessions.store') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5>Log Training Session</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Date of Training</label>
                            <input type="date" name="training_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Trainee</label>
                            <select name="trainee_id" class="form-control select2" required>
                                @foreach ($trainees as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}
                                        ({{ $t->department->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trainer (Leader)</label>
                            <select name="trainer_id" class="form-control">
                                <option value="">Optional for self training</option>
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label>Register No.</label>
                                <input type="text" name="register_no" class="form-control" placeholder="e.g. R-01"
                                    required>
                            </div>
                            <div class="col-6">
                                <label>Page No.</label>
                                <input type="text" name="page_no" class="form-control" placeholder="e.g. 45"
                                    required>
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

        <div class="modal fade" id="reassignTrainingModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="POST" class="modal-content" id="reassignTrainingForm">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reassignment_scope" id="reassignmentScope" value="same">
                    <input type="hidden" name="reassignment_target" id="reassignmentTarget" value="">

                    <div class="modal-header">
                        <h5 class="modal-title">Re-assign Training</h5>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-light border">
                            <div class="small text-muted text-uppercase mb-1">Failed training</div>
                            <div class="font-weight-bold" id="reassignCurrentTrainingName">N/A</div>
                            <div class="small text-muted" id="reassignCurrentTrainingStatus"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="reassign-choice-card w-100 mb-0">
                                    <input type="radio" name="reassign_choice" value="same" id="reassignSameChoice">
                                    <div class="choice-title">Same failed training</div>
                                    <div class="choice-subtitle" id="reassignSameSubtitle"></div>
                                </label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="reassign-choice-card w-100 mb-0">
                                    <input type="radio" name="reassign_choice" value="other"
                                        id="reassignOtherChoice">
                                    <div class="choice-title">Another regular training</div>
                                    <div class="choice-subtitle">Choose an active regular training you are not already
                                        enrolled in.</div>
                                </label>
                            </div>
                        </div>

                        <div id="reassignOtherSection">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Available regular trainings</h6>
                                <small class="text-muted">Only active regular trainings you have not taken yet are
                                    shown.</small>
                            </div>

                            <div class="reassign-training-list" id="reassignTrainingList">
                                <div class="text-muted small border rounded p-3">
                                    Choose a failed row to load eligible trainings.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="reassignSubmitButton"
                            disabled>Re-assign</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <style>
        .training-status-action {
            min-width: 150px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.25rem;
            border: 1px solid #dc3545;
            background: #dc3545;
            color: #ffffff;
            padding: 0.35rem 0.75rem;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            line-height: 1;
            text-decoration: none;
        }

        .training-status-action .hover-label {
            display: none;
        }

        .training-status-action .default-label,
        .training-status-action .hover-label {
            pointer-events: none;
            white-space: nowrap;
        }

        .training-status-action:hover {
            background-color: #16a34a !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .training-status-action:hover,
        .training-status-action:focus {
            text-decoration: none;
        }

        .training-status-action:hover .default-label {
            display: none;
        }

        .training-status-action:hover .hover-label {
            display: inline;
        }

        .training-status-action.disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .reassign-choice-card {
            border: 1px solid #d8dee9;
            border-radius: 10px;
            padding: 14px 16px;
            background: #ffffff;
            cursor: pointer;
            display: block;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .reassign-choice-card:hover {
            border-color: #6b7280;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .reassign-choice-card input {
            margin-right: 8px;
        }

        .choice-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .choice-subtitle {
            font-size: 0.82rem;
            color: #6c757d;
        }

        .reassign-training-list {
            display: grid;
            gap: 10px;
            max-height: 320px;
            overflow-y: auto;
            padding-right: 2px;
        }

        .reassign-training-option {
            width: 100%;
            text-align: left;
            border: 1px solid #d8dee9;
            border-radius: 10px;
            background: #ffffff;
            padding: 12px 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .reassign-training-option:hover:not(.is-disabled) {
            border-color: #2563eb;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12);
            transform: translateY(-1px);
        }

        .reassign-training-option.is-selected {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .reassign-training-option.is-disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #f8fafc;
        }
    </style>

    @push('scripts')
        <script>
            (function() {
                const modal = document.getElementById('reassignTrainingModal');
                if (!modal) {
                    return;
                }

                const form = document.getElementById('reassignTrainingForm');
                const assignmentRouteTemplate = @json(route('sessions.reassign', ['assignment' => '__ASSIGNMENT__']));
                const currentTrainingName = document.getElementById('reassignCurrentTrainingName');
                const currentTrainingStatus = document.getElementById('reassignCurrentTrainingStatus');
                const sameSubtitle = document.getElementById('reassignSameSubtitle');
                const sameChoice = document.getElementById('reassignSameChoice');
                const otherChoice = document.getElementById('reassignOtherChoice');
                const scopeInput = document.getElementById('reassignmentScope');
                const targetInput = document.getElementById('reassignmentTarget');
                const submitButton = document.getElementById('reassignSubmitButton');
                const otherSection = document.getElementById('reassignOtherSection');
                const trainingList = document.getElementById('reassignTrainingList');
                const trainingMap = @json($reassignmentTrainingMap ?? []);

                const state = {
                    assignmentId: null,
                    traineeId: null,
                    currentTrainingId: null,
                    currentTrainingName: '',
                    currentTrainingExpired: false,
                };

                function setFormAction(assignmentId) {
                    form.action = assignmentRouteTemplate.replace('__ASSIGNMENT__', assignmentId);
                }

                function clearOptionSelection() {
                    Array.from(modal.querySelectorAll('.reassign-training-option')).forEach((button) => {
                        button.classList.remove('is-selected');
                    });
                }

                function renderTrainingOptions() {
                    const options = trainingMap[state.traineeId] || [];
                    const eligibleOptions = options.filter((item) => String(item.id) !== String(state.currentTrainingId));

                    if (!eligibleOptions.length) {
                        trainingList.innerHTML =
                            '<div class="text-muted small border rounded p-3">No eligible regular trainings are available for reassignment.</div>';
                        return;
                    }

                    trainingList.innerHTML = eligibleOptions.map((item) => {
                        const isDisabled = item.expired ? 'disabled' : '';
                        const disabledClass = item.expired ? 'is-disabled' : '';
                        const badgeClass = item.expired ? 'badge-secondary' : 'badge-success';
                        const badgeLabel = item.expired ? 'Ended' : 'Available';
                        const extraNote = item.expired ?
                            '<small class="text-danger d-block mt-2">This training has ended and cannot be selected.</small>' :
                            '';

                        return `
                        <button
                            type="button"
                            class="reassign-training-option ${disabledClass}"
                            data-training-id="${item.id}"
                            data-training-name="${item.name}"
                            data-training-expired="${item.expired ? '1' : '0'}"
                            ${isDisabled}
                        >
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="font-weight-bold">${item.name}</div>
                                    <small class="text-muted">${item.type_label} training</small>
                                </div>
                                <span class="badge ${badgeClass}">${badgeLabel}</span>
                            </div>
                            ${extraNote}
                        </button>
                    `;
                    });

                    Array.from(modal.querySelectorAll('.reassign-training-option')).forEach((button) => {
                        button.addEventListener('click', function() {
                            if (button.disabled) {
                                return;
                            }

                            Array.from(modal.querySelectorAll('.reassign-training-option')).forEach((
                                item) => item.classList.remove('is-selected'));
                            button.classList.add('is-selected');
                            targetInput.value = button.dataset.trainingId || '';
                            setScope('other');
                            otherChoice.checked = true;
                            updateSubmitState();
                        });
                    });
                }

                function updateSubmitState() {
                    const scope = scopeInput.value;
                    const hasTarget = targetInput.value.trim() !== '';
                    submitButton.disabled = scope === 'same' ?
                        state.currentTrainingExpired :
                        !hasTarget;
                }

                function setScope(scope) {
                    scopeInput.value = scope;
                    sameChoice.checked = scope === 'same';
                    otherChoice.checked = scope === 'other';

                    if (scope === 'same') {
                        targetInput.value = state.currentTrainingId || '';
                    }

                    otherSection.style.display = scope === 'other' ? 'block' : 'none';
                    updateSubmitState();
                }

                sameChoice?.addEventListener('change', function() {
                    setScope('same');
                    clearOptionSelection();
                });

                otherChoice?.addEventListener('change', function() {
                    setScope('other');
                    clearOptionSelection();
                });

                document.querySelectorAll('.js-open-reassign-modal').forEach((button) => {
                    button.addEventListener('click', function() {
                        state.assignmentId = button.dataset.assignmentId || '';
                        state.traineeId = button.dataset.traineeId || '';
                        state.currentTrainingId = button.dataset.trainingId || '';
                        state.currentTrainingName = button.dataset.trainingName || 'N/A';
                        state.currentTrainingExpired = button.dataset.trainingExpired === '1';

                        setFormAction(state.assignmentId);
                        renderTrainingOptions();
                        currentTrainingName.textContent = state.currentTrainingName;
                        currentTrainingStatus.textContent = button.dataset.reassignmentNote || '';
                        sameSubtitle.textContent = state.currentTrainingExpired ?
                            'This training is expired, so you must choose another active training.' :
                            'Re-assign the trainee back to the same failed training.';

                        sameChoice.disabled = state.currentTrainingExpired;
                        if (state.currentTrainingExpired) {
                            sameChoice.checked = false;
                            otherChoice.checked = true;
                            setScope('other');
                            targetInput.value = '';
                        } else {
                            sameChoice.checked = true;
                            otherChoice.checked = false;
                            targetInput.value = state.currentTrainingId;
                            setScope('same');
                        }

                        clearOptionSelection();
                        updateSubmitState();
                    });
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    state.assignmentId = null;
                    state.traineeId = null;
                    state.currentTrainingId = null;
                    state.currentTrainingName = '';
                    state.currentTrainingExpired = false;
                    form.reset();
                    scopeInput.value = 'same';
                    targetInput.value = '';
                    otherSection.style.display = 'none';
                    sameChoice.disabled = false;
                    submitButton.disabled = true;
                    trainingList.innerHTML =
                        '<div class="text-muted small border rounded p-3">Choose a failed row to load eligible trainings.</div>';
                    clearOptionSelection();
                });
            })();
        </script>
    @endpush
@endsection
