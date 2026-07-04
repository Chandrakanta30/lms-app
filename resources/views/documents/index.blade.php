@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3 class="page-title">Global Document Master</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocModal">
                <i class="mdi mdi-plus"></i> Upload New Document
            </button>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Doc Number</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Uploaded By</th>
                                <th>Reviewed By</th>
                                <th>Reviewed At</th>
                                <th>Pool</th>
                                <th>Actions</th>
                            </tr>
                        </thead>


                        <tbody>
                            @forelse($documents as $doc)
                                <tr>
                                    <td><strong>{{ $doc->doc_number }}</strong></td>
                                    <td>{{ $doc->doc_name }}</td>
                                    <td>
                                        <span class="badge badge-outline-info text-dark">
                                            {{ $doc->doc_type }}
                                        </span>
                                    </td>

                                    <!-- ✅ NEW COLUMNS -->
                                    <td>{{ $doc->uploader->name ?? 'N/A' }}</td>
                                    <td>{{ $doc->reviewer->name ?? 'Not Reviewed' }}</td>
                                    <td>
                                        {{ $doc->reviewed_at ? $doc->reviewed_at->diffForHumans() : 'Not Reviewed' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('master-questions.index', $doc->id) }}"
                                            class="btn btn-sm btn-dark">
                                            Manage Pool ({{ $doc->questions_count }})
                                        </a>
                                    </td>

                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('master-documents.show', $doc->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> Show
                                            </a>

                                            @if (!$doc->reviewed_by)
                                                <button type="button" class="btn btn-success btn-sm open-review-sign-modal"
                                                    data-bs-toggle="modal" data-bs-target="#reviewSignModal"
                                                    data-doc-id="{{ $doc->id }}" data-doc-name="{{ $doc->doc_name }}"
                                                    data-review-url="{{ route('master-documents.review', $doc->id) }}">
                                                    <i class="mdi mdi-check-decagram"></i>
                                                    {{ $doc->reviewed_by ? 'Re-Review' : 'Review' }}
                                                </button>
                                            @ENDIF

                                            <form action="{{ route('master-documents.destroy', $doc->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No documents found in the master pool.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>



                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addDocModal" tabindex="-1" aria-labelledby="addDocModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('master-documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDocModalLabel">Upload Master Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">Document Name</label>
                            <input type="text" name="doc_name" class="form-control" placeholder="e.g. Fire Safety SOP"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Doc Number</label>
                                    <input type="text" name="doc_number" class="form-control" placeholder="SOP-001"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="doc_type" class="form-control">
                                        <option value="SOP">SOP</option>
                                        <option value="Protocol">Protocol</option>
                                        <option value="PPT">PPT</option>
                                        <option value="Others">Others</option>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Department</label>

                                    <select name="department_id" class="form-control" required>

                                        <option value="">Select Department</option>

                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">
                                                {{ $department->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Sub Department</label>
                                    @php
                                        $selectedSubdepartments = old('subdepartment_id', []);
                                        if (!is_array($selectedSubdepartments)) {
                                            $selectedSubdepartments = filled($selectedSubdepartments)
                                                ? [$selectedSubdepartments]
                                                : [];
                                        }
                                    @endphp
                                    <div class="subdept-dropdown-wrapper" style="position:relative;">
                                        <div id="subdept-toggle" onclick="toggleDropdown('subdept-dropdown')"
                                            style="min-height:48px; border-radius:14px; border:1px solid rgba(148,163,184,0.22); background:rgba(255,255,255,0.88); padding:8px 14px; cursor:pointer; display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
                                            <span id="subdept-placeholder"
                                                style="color:#94a3b8; font-size:0.9rem;">Select sub departments</span>
                                        </div>
                                        <div id="subdept-dropdown"
                                            style="display:none; position:absolute; z-index:999; width:100%; top:calc(100% + 4px); background:#fff; border:1px solid rgba(148,163,184,0.3); border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,0.1); overflow:hidden;">
                                            <div style="padding:10px;">
                                                <input type="text" id="subdept-search" oninput="filterOptions('subdept')"
                                                    placeholder="Search..."
                                                    style="width:100%; border-radius:10px; border:1px solid rgba(148,163,184,0.3); padding:6px 12px; font-size:0.85rem;">
                                            </div>
                                            <div style="max-height:200px; overflow-y:auto; padding:0 6px 8px;">
                                                @forelse ($subdepartments ?? [] as $subdept)
                                                    <label id="subdept-item-{{ $subdept->id }}"
                                                        style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px; cursor:pointer; font-weight:500; color:#0f172a; margin:0;"
                                                        onmouseover="this.style.background='rgba(37,99,235,0.07)'"
                                                        onmouseout="this.style.background='transparent'">
                                                        <input class="subdept-checkbox" type="checkbox"
                                                            name="subdepartment_id[]" value="{{ $subdept->id }}"
                                                            onchange="updateDropdownTags('subdept')"
                                                            {{ in_array((string) $subdept->id, array_map('strval', $selectedSubdepartments)) ? 'checked' : '' }}
                                                            style="width:16px; height:16px; cursor:pointer; accent-color:#2563eb;">
                                                        {{ $subdept->name }}
                                                    </label>
                                                @empty
                                                    <div class="text-muted small p-2">No sub departments found.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Section</label>
                                    @php
                                        $selectedSections = old('section_id', []);
                                        if (!is_array($selectedSections)) {
                                            $selectedSections = filled($selectedSections)
                                                ? [$selectedSections]
                                                : [];
                                        }
                                    @endphp
                                    <div class="section-dropdown-wrapper" style="position:relative;">
                                        <div id="section-toggle" onclick="toggleDropdown('section-dropdown')"
                                            style="min-height:48px; border-radius:14px; border:1px solid rgba(148,163,184,0.22); background:rgba(255,255,255,0.88); padding:8px 14px; cursor:pointer; display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
                                            <span id="section-placeholder"
                                                style="color:#94a3b8; font-size:0.9rem;">Select sections</span>
                                        </div>
                                        <div id="section-dropdown"
                                            style="display:none; position:absolute; z-index:999; width:100%; top:calc(100% + 4px); background:#fff; border:1px solid rgba(148,163,184,0.3); border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,0.1); overflow:hidden;">
                                            <div style="padding:10px;">
                                                <input type="text" id="section-search" oninput="filterOptions('section')"
                                                    placeholder="Search..."
                                                    style="width:100%; border-radius:10px; border:1px solid rgba(148,163,184,0.3); padding:6px 12px; font-size:0.85rem;">
                                            </div>
                                            <div style="max-height:200px; overflow-y:auto; padding:0 6px 8px;">
                                                @forelse ($sections ?? [] as $section)
                                                    <label id="section-item-{{ $section->sec_id }}"
                                                        style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px; cursor:pointer; font-weight:500; color:#0f172a; margin:0;"
                                                        onmouseover="this.style.background='rgba(37,99,235,0.07)'"
                                                        onmouseout="this.style.background='transparent'">
                                                        <input class="section-checkbox" type="checkbox"
                                                            name="section_id[]" value="{{ $section->sec_id }}"
                                                            onchange="updateDropdownTags('section')"
                                                            {{ in_array((string) $section->sec_id, array_map('strval', $selectedSections)) ? 'checked' : '' }}
                                                            style="width:16px; height:16px; cursor:pointer; accent-color:#2563eb;">
                                                        {{ $section->name }}
                                                    </label>
                                                @empty
                                                    <div class="text-muted small p-2">No sections found.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Read Time</label>

                                    <input type="text" name="read_time" class="form-control"
                                        placeholder="Enter read time (e.g. 5 min)" required>
                                </div>
                            </div>
                        </div>




                        <div class="form-group mb-3">
                            <label class="form-label">Select File (PDF/Doc)</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save to Master Pool</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="reviewSignModal" tabindex="-1" aria-labelledby="reviewSignModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewSignModalLabel">Review & Approve Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2 text-muted">You are about to review:</p>
                    <h6 id="reviewTargetDocName" class="mb-3"></h6>

                    <div id="reviewSignaturePreview" class="d-none">
                        <div class="signature-box p-2 text-center"
                            style="border: 1px dashed #28a745; background: #f0fff4; border-radius: 4px;">
                            <i class="fas fa-certificate text-success mb-1" title="Verified Signature"></i>
                            <div class="signature-text"
                                style="font-family: 'Dancing Script', cursive; font-size: 1.2rem; color: #003366;">
                                {{ auth()->user()->name ?? 'Reviewer' }}
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Digital signature captured. Finalizing review...</small>
                    </div>

                    <form id="reviewSignForm" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="reviewSignApproveBtn">
                        <i class="fas fa-signature mr-1"></i> Sign & Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var reviewSignModal = document.getElementById('reviewSignModal');
            var reviewTargetDocName = document.getElementById('reviewTargetDocName');
            var reviewSignForm = document.getElementById('reviewSignForm');
            var reviewSignaturePreview = document.getElementById('reviewSignaturePreview');
            var reviewSignApproveBtn = document.getElementById('reviewSignApproveBtn');
            var selectedReviewUrl = null;

            document.querySelectorAll('.open-review-sign-modal').forEach(function(button) {
                button.addEventListener('click', function() {
                    selectedReviewUrl = this.getAttribute('data-review-url');
                    reviewTargetDocName.textContent = this.getAttribute('data-doc-name') ||
                        'Selected Document';
                    reviewSignaturePreview.classList.add('d-none');
                    reviewSignApproveBtn.disabled = false;
                });
            });

            reviewSignApproveBtn.addEventListener('click', function() {
                if (!selectedReviewUrl) {
                    return;
                }

                this.disabled = true;
                reviewSignaturePreview.classList.remove('d-none');
                reviewSignForm.setAttribute('action', selectedReviewUrl);

                setTimeout(function() {
                    var modalInstance = bootstrap.Modal.getOrCreateInstance(reviewSignModal);
                    modalInstance.hide();
                    reviewSignForm.submit();
                }, 700);
            });
        })();

        function toggleDropdown(dropdownId) {
            var dropdown = document.getElementById(dropdownId);
            if (!dropdown) {
                return;
            }

            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }

        function filterOptions(prefix) {
            var input = document.getElementById(prefix + '-search');
            var query = (input ? input.value : '').toLowerCase();

            document.querySelectorAll('[id^="' + prefix + '-item-"]').forEach(function(el) {
                el.style.display = el.innerText.toLowerCase().includes(query) ? 'flex' : 'none';
            });
        }

        function updateDropdownTags(prefix) {
            var toggle = document.getElementById(prefix + '-toggle');
            var placeholder = document.getElementById(prefix + '-placeholder');
            var checked = document.querySelectorAll('.' + prefix + '-checkbox:checked');

            if (!toggle || !placeholder) {
                return;
            }

            toggle.querySelectorAll('.' + prefix + '-tag').forEach(function(tag) {
                tag.remove();
            });

            if (checked.length === 0) {
                placeholder.style.display = 'inline';
                return;
            }

            placeholder.style.display = 'none';
            checked.forEach(function(cb) {
                var tag = document.createElement('span');
                tag.className = prefix + '-tag';
                tag.style =
                    'background:rgba(37,99,235,0.1); color:#2563eb; padding:3px 10px; border-radius:999px; font-size:0.8rem; font-weight:600;';
                tag.innerText = cb.closest('label').innerText.trim();
                toggle.appendChild(tag);
            });
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.subdept-dropdown-wrapper')) {
                var subdeptDropdown = document.getElementById('subdept-dropdown');
                if (subdeptDropdown) {
                    subdeptDropdown.style.display = 'none';
                }
            }

            if (!e.target.closest('.section-dropdown-wrapper')) {
                var sectionDropdown = document.getElementById('section-dropdown');
                if (sectionDropdown) {
                    sectionDropdown.style.display = 'none';
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            updateDropdownTags('subdept');
            updateDropdownTags('section');
        });
    </script>
@endsection
