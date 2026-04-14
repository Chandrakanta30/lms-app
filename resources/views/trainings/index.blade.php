@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="card-title mb-0">Training Management</h4>
                <a href="{{ route('trainings.create') }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-plus"></i> Add New Program
                </a>
            </div>

            <div class="accordion custom-accordion" id="trainingAccordion">
                @foreach($trainings as $training)
                <div class="card border mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-link text-decoration-none text-dark font-weight-bold p-0" 
                                    data-toggle="collapse" 
                                    data-target="#collapse{{ $training->id }}">
                                <i class="mdi mdi-chevron-down-circle-outline mr-2 text-primary"></i>
                                {{ $training->name }}
                            </button>
                            <span class="badge badge-outline-secondary ml-2">{{ $training->steps->count() }} Steps</span>
                        </div>
                        
                        <div class="btn-group">
                            <a href="{{ route('admin.modules.linkDocs', $training->id) }}" class="btn btn-sm btn-outline-info" title="Link Documents">
                                <i class="mdi mdi-link-variant"></i>
                            </a>
                            <a href="{{ route('manage-trainers', $training->id) }}" class="btn btn-sm btn-outline-primary">
                                Trainers <span class="badge badge-primary ml-1">{{ $training->trainers_count ?? $training->trainers->count() }}</span>
                            </a>
                            <a href="{{ route('manage-users', $training->id) }}" class="btn btn-sm btn-outline-success">
                                Users <span class="badge badge-success ml-1">{{ $training->trainees_count ?? $training->trainees->count() }}</span>
                            </a>




                            <form action="{{ route('trainings.toggle-status', $training->id) }}" method="POST" class="mr-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $training->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                        title="{{ $training->is_active ? 'Click to Disable' : 'Click to Enable' }}">
                                    <i class="fas {{ $training->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    {{ $training->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                            <a href="{{ route('trainings.edit', $training->id) }}" class="btn btn-sm btn-light ml-1 text-info"><i class="mdi mdi-pencil"></i></a>
                            
                            <form action="{{ route('trainings.destroy', $training->id) }}" method="POST" class="d-inline ml-1">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger" onclick="return confirm('Delete Program?')">
                                    <i class="mdi mdi-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="collapse{{ $training->id }}" class="collapse" data-parent="#trainingAccordion">
                        <div class="card-body bg-light">
                            <ul class="nav nav-pills mb-3" id="pills-tab-{{ $training->id }}" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active py-1 px-3" data-toggle="pill" href="#steps-{{ $training->id }}">Steps</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3" data-toggle="pill" href="#trainers-{{ $training->id }}">Trainers</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3" data-toggle="pill" href="#users-{{ $training->id }}">Enrolled Users</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1 px-3" data-toggle="pill" href="#docs-{{ $training->id }}">Exam Pool</a>
                                </li>
                            </ul>

                            <div class="tab-content bg-white p-3 border rounded shadow-sm">
                                <div class="tab-pane fade show active" id="steps-{{ $training->id }}">
                                    <div class="list-group list-group-flush">
                                        @foreach($training->steps as $step)
                                        <div class="list-group-item d-flex align-items-center">
                                            <div class="mr-3"><b class="text-primary">{{ $loop->iteration }}</b></div>
                                            <div>{{ $step->name }}</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="trainers-{{ $training->id }}">
                                    @forelse($training->trainers as $trainer)
                                        <div class="d-inline-block border rounded p-2 m-1 bg-light">
                                            <i class="mdi mdi-account-star text-primary"></i> {{ $trainer->name }}
                                        </div>
                                    @empty
                                        <p class="text-muted small">No trainers assigned.</p>
                                    @endforelse
                                </div>

                                <div class="tab-pane fade" id="users-{{ $training->id }}">
                                    <div class="row">
                                        @forelse($training->trainees as $user)
                                            <div class="col-md-4 mb-2">
                                                <div class="small p-2 border-left border-success bg-light">
                                                    {{ $user->name }} <br>
                                                    <span class="text-muted" style="font-size: 0.8rem;">{{ $user->email }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-muted small">No users enrolled yet.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="docs-{{ $training->id }}">
                                    <table class="table table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Document Name</th>
                                                <th>Question Quota</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($training->documents as $doc)
                                                <tr>
                                                    <td>{{ $doc->doc_name }}</td>
                                                    <td><span class="badge badge-dark">{{ $doc->pivot->question_quota }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-muted">No documents linked.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .custom-accordion .card-header { border-bottom: 0; transition: background 0.3s; }
    .custom-accordion .card-header:hover { background-color: #f8f9fa !important; }
    .nav-pills .nav-link { font-size: 0.85rem; color: #6c757d; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background-color: #4b49ac !important; color: white; }
    .badge-outline-secondary { border: 1px solid #6c757d; color: #6c757d; background: transparent; }
</style>
@endsection