@extends('partials.app')
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Edit Training Program</h4>

                    {{-- Added enctype for file uploads --}}
                    <form action="{{ route('trainings.update', $training->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Training Program Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $training->name }}" required>
                        </div>

                        <div class="form-group">
                            <label>Workflow Status</label>
                            <select name="status" class="form-control" required>
                                @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}" {{ old('status', $training->status ?? 'created') === $statusOption ? 'selected' : '' }}>
                                    {{ ucfirst($statusOption) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Training Type</label>
                            <select name="training_type" id="training_type" class="form-control">
                                <option value="classroom" {{ $training->training_type == 'classroom' ? 'selected' : '' }}>Classroom / Instructor Led</option>
                                <option value="self_training" {{ $training->training_type == 'self_training' ? 'selected' : '' }}>Self Training (E-Learning)</option>
                            </select>
                        </div>




                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date"
                                name="start_date"
                                class="form-control"
                                value="{{ old('start_date', $training->start_date ? \Illuminate\Support\Carbon::parse($training->start_date)->format('Y-m-d') : '') }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label>End Date (Deadline)</label>
                            <input type="date"
                                name="end_date"
                                class="form-control"
                                value="{{ old('end_date', $training->end_date ? \Illuminate\Support\Carbon::parse($training->end_date)->format('Y-m-d') : '') }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control"
                                value="{{ old('start_time', $training->start_time) }}">
                        </div>


                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control"
                                value="{{ old('end_time', $training->end_time) }}">
                        </div>





                        <hr>
                        <h5>Departmental Steps</h5>
                        <p class="text-muted small">Optional. Leave this blank if the training program does not need step-by-step departmental breakdown.</p>
                        <div id="step-container">
                            @forelse($training->steps as $index => $step)
                            <div class="input-group mb-2 step-row">
                                <div class="input-group-prepend">
                                    <span class="input-group-text step-index">{{ $index + 1 }}</span>
                                </div>
                                <input type="text" name="step_names[]" class="form-control" value="{{ $step->name }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-step"><i class="mdi mdi-delete"></i></button>
                                </div>
                            </div>
                            @empty
                            <div class="input-group mb-2 step-row">
                                <div class="input-group-prepend">
                                    <span class="input-group-text step-index">1</span>
                                </div>
                                <input type="text" name="step_names[]" class="form-control" placeholder="Optional step name">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-step"><i class="mdi mdi-delete"></i></button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-outline-info btn-sm mt-2" id="add-step">+ Add Step</button>



                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary mr-2">Update Program</button>
                            <a href="{{ route('trainings.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- Step Logic ---
    document.getElementById('add-step').addEventListener('click', function() {
        const container = document.getElementById('step-container');
        const rowCount = container.getElementsByClassName('step-row').length + 1;
        const div = document.createElement('div');
        div.className = "input-group mb-2 step-row";
        div.innerHTML = `<div class="input-group-prepend"><span class="input-group-text step-index">${rowCount}</span></div>
            <input type="text" name="step_names[]" class="form-control">
            <div class="input-group-append"><button type="button" class="btn btn-danger remove-step"><i class="mdi mdi-delete"></i></button></div>`;
        container.appendChild(div);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-step')) {
            e.target.closest('.step-row').remove();
            reindexSteps();
        }
    });

    function reindexSteps() {
        const indices = document.getElementsByClassName('step-index');
        for (let i = 0; i < indices.length; i++) {
            indices[i].innerText = i + 1;
        }
    }

    // --- Document Logic ---
    function getDocRowHtml(index) {
        return `<tr>
            <td><select name="docs[${index}][type]" class="form-control" required>
                <option value="SOP">SOP</option><option value="Protocol">Protocol</option>
                <option value="PPT">PPT</option><option value="Others">Others</option>
            </select></td>
            <td><input type="text" name="docs[${index}][name]" class="form-control" required></td>
            <td><input type="text" name="docs[${index}][number]" class="form-control"></td>
            <td><input type="text" name="docs[${index}][version]" class="form-control"></td>
            <td><input type="file" name="docs[${index}][file]" class="form-control" required></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
        </tr>`;
    }

    document.getElementById('training_type').addEventListener('change', function() {
        const section = document.getElementById('self_training_section');
        const tbody = document.querySelector('#docTable tbody');

        if (!section || !tbody) {
            return;
        }

        if (this.value === 'self_training') {
            section.style.display = 'block';
            if (tbody.children.length === 0) tbody.insertAdjacentHTML('beforeend', getDocRowHtml(Date.now()));
        } else {
            section.style.display = 'none';
            tbody.innerHTML = '';
        }
    });

    $(document).on('click', '.addRow', function() {
        if (!document.querySelector('#docTable tbody')) {
            return;
        }
        $('#docTable tbody').append(getDocRowHtml(Date.now()));
    });

    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
    });
</script>
@endpush
@endsection