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
                                    <label class="form-label">Section</label>

                                    <select name="section_id" class="form-control" required>

                                        <option value="">Select Section</option>

                                        @foreach ($sections as $section)
                                            <option value="{{ $section->sec_id }}">
                                                {{ $section->name }}
                                            </option>
                                        @endforeach

                                    </select>
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
    </script>
@endsection
