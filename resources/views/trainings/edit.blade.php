@extends('partials.app')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Training Program</h4>

                        {{-- Added enctype for file uploads --}}
                        <form action="{{ route('trainings.update', $training->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label>Training Program Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $training->name }}"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Workflow Status</label>
                                <select name="status" class="form-control" required>
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}"
                                            {{ old('status', $training->status ?? 'created') === $statusOption ? 'selected' : '' }}>
                                            {{ ucfirst($statusOption) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Training Type</label>
                                <select name="training_type" id="training_type" class="form-control">
                                    <option value="classroom"
                                        {{ $training->training_type == 'classroom' ? 'selected' : '' }}>Classroom /
                                        Instructor Led</option>
                                    <option value="self_training"
                                        {{ $training->training_type == 'self_training' ? 'selected' : '' }}>Self Training
                                        (E-Learning)</option>
                                </select>
                            </div>

                            @if (!$training->annual_parent_id)
                                <div class="form-group mt-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_annual" value="1" id="is_annual"
                                            {{ $training->is_anuual ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_annual">
                                            Annual Training Program
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div id="annual_fields_section"
                                class="row {{ $training->annual_parent_id ? '' : ($training->is_anuual ? '' : 'd-none') }}">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department_id" class="form-control"
                                            {{ $training->annual_parent_id ? 'disabled' : '' }}>
                                            <option value="">Select Department</option>
                                            @foreach ($departments ?? [] as $dept)
                                                <option value="{{ $dept->id }}"
                                                    {{ $training->department_id == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sub Department</label>
                                        @php
                                            $selectedSubdepartments = old('subdepartment_id', $training->subdepartment_id ?? []);
                                            if (!is_array($selectedSubdepartments)) {
                                                $selectedSubdepartments = filled($selectedSubdepartments)
                                                    ? [$selectedSubdepartments]
                                                    : [];
                                            }
                                        @endphp
                                        <div class="subdept-dropdown-wrapper" style="position:relative;">
                                            <div id="subdept-toggle" onclick="toggleSubdeptDropdown()"
                                                style="min-height:48px; border-radius:14px; border:1px solid rgba(148,163,184,0.22); background:rgba(255,255,255,0.88); padding:8px 14px; cursor:pointer; display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
                                                <span id="subdept-placeholder"
                                                    style="color:#94a3b8; font-size:0.9rem;">Select sub departments</span>
                                            </div>
                                            <div id="subdept-dropdown"
                                                style="display:none; position:absolute; z-index:999; width:100%; top:calc(100% + 4px); background:#fff; border:1px solid rgba(148,163,184,0.3); border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,0.1); overflow:hidden;">
                                                <div style="padding:10px;">
                                                    <input type="text" id="subdept-search" oninput="filterSubdepts()"
                                                        placeholder="Search..."
                                                        style="width:100%; border-radius:10px; border:1px solid rgba(148,163,184,0.3); padding:6px 12px; font-size:0.85rem;">
                                                </div>
                                                <div style="max-height:200px; overflow-y:auto; padding:0 6px 8px;">
                                                    @forelse ($subdepartments ?? [] as $sub)
                                                        <label id="subdept-item-{{ $sub->id }}"
                                                            style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px; cursor:pointer; font-weight:500; color:#0f172a; margin:0;"
                                                            onmouseover="this.style.background='rgba(37,99,235,0.07)'"
                                                            onmouseout="this.style.background='transparent'">
                                                            <input class="subdepartment-checkbox" type="checkbox"
                                                                name="subdepartment_id[]" value="{{ $sub->id }}"
                                                                onchange="updateSubdeptTags()"
                                                                {{ in_array((string) $sub->id, array_map('strval', $selectedSubdepartments)) ? 'checked' : '' }}
                                                                {{ $training->annual_parent_id ? 'disabled' : '' }}
                                                                style="width:16px; height:16px; cursor:pointer; accent-color:#2563eb;">
                                                            {{ $sub->name }}
                                                        </label>
                                                    @empty
                                                        <div class="text-muted small p-2">No sub departments found.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Frequency</label>
                                        <select name="frequency" class="form-control"
                                            {{ $training->annual_parent_id ? 'disabled' : '' }}>
                                            <option value="">Select Frequency</option>
                                            <option value="monthly"
                                                {{ $training->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="quarterly"
                                                {{ $training->frequency == 'quarterly' ? 'selected' : '' }}>Quarterly
                                            </option>
                                            <option value="half_yearly"
                                                {{ $training->frequency == 'half_yearly' ? 'selected' : '' }}>Half Yearly
                                            </option>
                                            <option value="yearly"
                                                {{ $training->frequency == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ old('start_date', $training->start_date ? \Illuminate\Support\Carbon::parse($training->start_date)->format('Y-m-d') : '') }}"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>End Date (Deadline)</label>
                                <input type="date" name="end_date" class="form-control"
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
                            <p class="text-muted small">Optional. Leave this blank if the training program does not need
                                step-by-step departmental breakdown.</p>
                            <div id="step-container">
                                @forelse($training->steps as $index => $step)
                                    <div class="input-group mb-2 step-row">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text step-index">{{ $index + 1 }}</span>
                                        </div>
                                        <input type="text" name="step_names[]" class="form-control"
                                            value="{{ $step->name }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger remove-step"><i
                                                    class="mdi mdi-delete"></i></button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="input-group mb-2 step-row">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text step-index">1</span>
                                        </div>
                                        <input type="text" name="step_names[]" class="form-control"
                                            placeholder="Optional step name">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger remove-step"><i
                                                    class="mdi mdi-delete"></i></button>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-outline-info btn-sm mt-2" id="add-step">+ Add
                                Step</button>



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
                div.innerHTML =
                    `<div class="input-group-prepend"><span class="input-group-text step-index">${rowCount}</span></div>
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

            function toggleSubdeptDropdown() {
                const dropdown = document.getElementById('subdept-dropdown');
                if (!dropdown) {
                    return;
                }

                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            }

            function filterSubdepts() {
                const search = document.getElementById('subdept-search');
                if (!search) {
                    return;
                }

                const query = search.value.toLowerCase();
                document.querySelectorAll('[id^="subdept-item-"]').forEach((item) => {
                    item.style.display = item.innerText.toLowerCase().includes(query) ? 'flex' : 'none';
                });
            }

            function updateSubdeptTags() {
                const toggle = document.getElementById('subdept-toggle');
                const placeholder = document.getElementById('subdept-placeholder');
                const checked = document.querySelectorAll('.subdepartment-checkbox:checked');

                if (!toggle || !placeholder) {
                    return;
                }

                toggle.querySelectorAll('.subdept-tag').forEach((tag) => tag.remove());

                if (checked.length === 0) {
                    placeholder.style.display = 'inline';
                    return;
                }

                placeholder.style.display = 'none';
                checked.forEach((checkbox) => {
                    const tag = document.createElement('span');
                    tag.className = 'subdept-tag';
                    tag.style =
                        'background:rgba(37,99,235,0.1); color:#2563eb; padding:3px 10px; border-radius:999px; font-size:0.8rem; font-weight:600;';
                    tag.innerText = checkbox.closest('label').innerText.trim();
                    toggle.appendChild(tag);
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const checkbox = document.getElementById('is_annual');
                const section = document.getElementById('annual_fields_section');

                if (!checkbox || !section) return;

                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        section.classList.remove('d-none');
                    } else {
                        section.classList.add('d-none');

                        // reset values
                        section.querySelectorAll('select').forEach(el => el.value = '');
                    }
                });

                updateSubdeptTags();

                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.subdept-dropdown-wrapper')) {
                        const dropdown = document.getElementById('subdept-dropdown');
                        if (dropdown) {
                            dropdown.style.display = 'none';
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
