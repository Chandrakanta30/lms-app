@extends('partials.app')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Define Annual Training Program</h4>
                        <form action="{{ route('trainings.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="is_annual" value="1">

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
                                    departmental checklist.</small>
                                <div id="step-container">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend"><span class="input-group-text">1</span></div>
                                        <input type="text" name="step_names[]" class="form-control"
                                            placeholder="Administration & Maintenance">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info mt-2" onclick="addStepField()">+
                                    Add Step</button>
                            </div>

                            <div class="form-group">
                                <label>Training Type</label>
                                <select name="training_type" class="form-control">
                                    <option value="classroom" {{ old('training_type') === 'classroom' ? 'selected' : '' }}>
                                        Classroom / Instructor Led</option>
                                    <option value="self_training"
                                        {{ old('training_type') === 'self_training' ? 'selected' : '' }}>
                                        Self Training (E-Learning)</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department_id" class="form-control" required>
                                            <option value="">Select Department</option>
                                            @foreach ($departments ?? [] as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sub Department</label>
                                        @php
                                            $selectedSubdepartments = old('subdepartment_id', []);
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
                                        <select name="frequency" class="form-control" required>
                                            <option value="">Select Frequency</option>

                                            <option value="quarterly">Quarterly</option>
                                            <option value="half_yearly">Half Yearly</option>

                                        </select>
                                    </div>
                                </div>
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

            function toggleSubdeptDropdown() {
                const dd = document.getElementById('subdept-dropdown');
                dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
            }

            function filterSubdepts() {
                const q = document.getElementById('subdept-search').value.toLowerCase();
                document.querySelectorAll('[id^="subdept-item-"]').forEach(el => {
                    el.style.display = el.innerText.toLowerCase().includes(q) ? 'flex' : 'none';
                });
            }

            function updateSubdeptTags() {
                const toggle = document.getElementById('subdept-toggle');
                const placeholder = document.getElementById('subdept-placeholder');
                const checked = document.querySelectorAll('.subdepartment-checkbox:checked');

                toggle.querySelectorAll('.subdept-tag').forEach(t => t.remove());

                if (checked.length === 0) {
                    placeholder.style.display = 'inline';
                } else {
                    placeholder.style.display = 'none';
                    checked.forEach(cb => {
                        const tag = document.createElement('span');
                        tag.className = 'subdept-tag';
                        tag.style =
                            'background:rgba(37,99,235,0.1); color:#2563eb; padding:3px 10px; border-radius:999px; font-size:0.8rem; font-weight:600;';
                        tag.innerText = cb.closest('label').innerText.trim();
                        toggle.appendChild(tag);
                    });
                }
            }

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.subdept-dropdown-wrapper')) {
                    document.getElementById('subdept-dropdown').style.display = 'none';
                }
            });

            document.addEventListener('DOMContentLoaded', updateSubdeptTags);
        </script>
    @endpush
@endsection
