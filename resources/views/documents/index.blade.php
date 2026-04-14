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
                            <th>Pool</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td><strong>{{ $doc->doc_number }}</strong></td>
                            <td>{{ $doc->doc_name }}</td>
                            <td><span class="badge badge-outline-info text-dark">{{ $doc->doc_type }}</span></td>
                            <td>
                                <a href="{{ route('master-questions.index', $doc->id) }}" class="btn btn-sm btn-dark">
                                    Manage Pool ({{ $doc->questions_count }})
                                </a>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-info btn-sm">View</a>
                                    <form action="{{ route('master-documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No documents found in the master pool.</td>
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
                        <input type="text" name="doc_name" class="form-control" placeholder="e.g. Fire Safety SOP" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Doc Number</label>
                                <input type="text" name="doc_number" class="form-control" placeholder="SOP-001" required>
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
@endsection