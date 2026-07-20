@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Define Training Program</h4>
                        <form action="{{ route('trainings.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="form_token" value="{{ $formToken }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Main Training Title</label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="e.g. Induction Training" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Workflow Status</label>
                                        <select name="status" class="form-control" required>
                                            @foreach ($statusOptions as $statusOption)
                                                <option value="{{ $statusOption }}"
                                                    {{ old('status', 'created') === $statusOption ? 'selected' : '' }}>
                                                    {{ ucfirst($statusOption) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Departmental Steps</label>
                                <small class="d-block text-muted mb-2">Optional. Add steps only if this program needs a
                                    departmental
                                    checklist.</small>
                                <div id="step-container">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend"><span class="input-group-text">1</span></div>
                                        <input type="text" name="step_names[]" class="form-control"
                                            placeholder="Administration & Maintenance">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info mt-2" onclick="addStepField()">+
                                    Add
                                    Step</button>
                            </div>
                            <div class="form-group">
                                <label>Training Type</label>
                                <select name="training_type" id="training_type" class="form-control">
                                    <option value="classroom" {{ old('training_type') === 'classroom' ? 'selected' : '' }}>
                                        Classroom
                                        / Instructor Led</option>
                                    <option value="self_training"
                                        {{ old('training_type') === 'self_training' ? 'selected' : '' }}>
                                        Self Training (E-Learning)</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ old('start_date') }}" required>
                                    </div>
                                </div>



                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>End Date (Deadline)</label>
                                        <input type="date" name="end_date" class="form-control"
                                            value="{{ old('end_date') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" name="start_time" class="form-control"
                                            value="{{ old('start_time') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" name="end_time" class="form-control"
                                            value="{{ old('end_time') }}">
                                    </div>
                                </div>
                            </div>


                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">Save Program</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let stepCount = 1;

            function addStepField() {
                stepCount++;
                const wrapper = document.getElementById('step-container');
                const div = document.createElement('div');
                div.className = "input-group mb-2";
                div.innerHTML = `
                <div class="input-group-prepend"><span class="input-group-text">${stepCount}</span></div>
                <input type="text" name="step_names[]" class="form-control">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">x</button>
                </div>
            `;
                wrapper.appendChild(div);
            }



            document.getElementById('training_type').addEventListener('change', function() {
                const selfTrainingSection = document.getElementById('self_training_section');
                if (selfTrainingSection) {
                    selfTrainingSection.style.display = (this.value === 'self_training') ? 'block' : 'none';
                }
            });
        </script>
    @endpush
@endsection
