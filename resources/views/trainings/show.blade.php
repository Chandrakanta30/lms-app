@extends('partials.app')

@section('title', 'View Training')

@php
    $statusMap = [
        'created' => ['label' => 'Created', 'class' => 'badge-dark'],
        'inreview' => ['label' => 'In Review', 'class' => 'badge-warning'],
        'reviewed' => ['label' => 'Reviewed', 'class' => 'badge-info'],
        'approved' => ['label' => 'Approved', 'class' => 'badge-success'],
    ];
    $statusMeta = $statusMap[$training->status ?? 'created'] ?? $statusMap['created'];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="page-intro mb-4">
        <span class="eyebrow">Training overview</span>
        <h2>{{ $training->name }}</h2>
        <p>This view brings together the workflow status, assigned trainers, enrolled trainee users, steps, and linked training documents in one place.</p>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Program Summary</h4>
                        <span class="badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Training Type</strong>
                            <div class="text-muted">{{ ucwords(str_replace('_', ' ', $training->training_type ?? 'classroom')) }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Workflow Status</strong>
                            <div class="text-muted">{{ $statusMeta['label'] }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Start Date</strong>
                            <div class="text-muted">{{ $training->start_date ? \Illuminate\Support\Carbon::parse($training->start_date)->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>End Date</strong>
                            <div class="text-muted">{{ $training->end_date ? \Illuminate\Support\Carbon::parse($training->end_date)->format('d M Y') : 'N/A' }}</div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Steps</h5>
                    <div class="list-group list-group-flush">
                        @forelse($training->steps as $step)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div><strong class="text-primary mr-2">{{ $step->step_number }}</strong>{{ $step->name }}</div>
                                <span class="text-muted small">{{ $statusMeta['label'] }}</span>
                            </div>
                        @empty
                            <div class="text-muted">No steps defined yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Quick Actions</h4>
                    <div class="d-grid gap-2">
                        <a href="{{ route('trainings.edit', $training->id) }}" class="btn btn-primary">Edit training</a>
                        <a href="{{ route('manage-trainers', $training->id) }}" class="btn btn-light">Manage trainers</a>
                        <a href="{{ route('manage-users', $training->id) }}" class="btn btn-light">Manage trainees</a>
                        <a href="{{ route('admin.modules.linkDocs', $training->id) }}" class="btn btn-light">Manage documents</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Assigned Trainers</h4>
                    <div class="row">
                        @forelse($training->trainers as $trainer)
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded h-100">
                                    <strong>{{ $trainer->name }}</strong>
                                    <div class="text-muted small">{{ $trainer->email }}</div>
                                    <div class="text-muted small">{{ $trainer->designation->name ?? 'No designation' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted">No trainers assigned.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Enrolled Trainees</h4>
                    <div class="row">
                        @forelse($training->trainees as $trainee)
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded h-100">
                                    <strong>{{ $trainee->name }}</strong>
                                    <div class="text-muted small">{{ $trainee->email }}</div>
                                    <div class="text-muted small">{{ $trainee->department->name ?? 'No department' }}</div>
                                    <div class="text-muted small">{{ $trainee->designation->name ?? 'No designation' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted">No trainee users assigned.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Training Documents</h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>Question Quota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($training->documents as $doc)
                                    <tr>
                                        <td>{{ $doc->doc_name }}</td>
                                        <td>{{ $doc->pivot->question_quota }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-muted">No training documents linked.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
