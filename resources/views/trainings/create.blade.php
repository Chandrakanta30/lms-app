@extends('partials.app') {{-- Points to resources/views/layouts/app.blade.php --}}
@section('content')
<div class="content-wrapper">

    <div class="card">
        <div class="card-body">
            <h4>Define Training Program</h4>
            <form action="{{ route('trainings.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Main Training Title</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Induction Training" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Workflow Status</label>
                            <select name="status" class="form-control" required>
                                @foreach($statusOptions as $statusOption)
                                    <option value="{{ $statusOption }}" {{ old('status', 'created') === $statusOption ? 'selected' : '' }}>
                                        {{ ucfirst($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Departmental Steps</label>
                    <small class="d-block text-muted mb-2">Optional. Add steps only if this program needs a departmental checklist.</small>
                    <div id="step-container">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend"><span class="input-group-text">1</span></div>
                            <input type="text" name="step_names[]" class="form-control" placeholder="Administration & Maintenance">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info mt-2" onclick="addStepField()">+ Add Step</button>
                </div>



<div class="form-group">
    <label>Training Type</label>
    <select name="training_type" id="training_type" class="form-control">
        <option value="classroom" {{ old('training_type') === 'classroom' ? 'selected' : '' }}>Classroom / Instructor Led</option>
        <option value="self_training" {{ old('training_type') === 'self_training' ? 'selected' : '' }}>Self Training (E-Learning)</option>
    </select>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>End Date (Deadline)</label>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
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


<script>
    // Configuration for the row HTML to avoid repetition
    function getDocRowHtml(index) {
        return `<tr>
            <td>
                <select name="docs[${index}][type]" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="SOP">SOP</option>
                    <option value="Protocol">Protocol</option>
                    <option value="PPT">PPT</option>
                    <option value="Others">Others</option>
                </select>
            </td>
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
            // Add the first row automatically if the table is empty
            if (tbody.children.length === 0) {
                tbody.insertAdjacentHTML('beforeend', getDocRowHtml(0));
            }
        } else {
            section.style.display = 'none';
            // Clear rows when hidden to prevent "not focusable" errors on submission
            tbody.innerHTML = '';
        }
    });

    // Handle Adding More Rows
    $(document).on('click', '.addRow', function() {
        if (!document.querySelector('#docTable tbody')) {
            return;
        }
        let rowCount = $('#docTable tbody tr').length;
        $('#docTable tbody').append(getDocRowHtml(rowCount));
    });

    // Handle Removing Rows
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        // If all rows are removed, the user can click '+' to add one back
    });
</script>
@endsection
