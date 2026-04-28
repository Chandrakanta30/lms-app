@extends('partials.app')

@section('title', $user->name . ' Profile')

@section('content')
<div class="content-wrapper user-profile-page">
    <div class="user-profile-hero card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="user-profile-avatar mr-4">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center mb-2">
                            <h2 class="mb-0 mr-3">{{ $user->name }}</h2>
                            @if($user->is_trainer)
                                <span class="badge badge-success px-3 py-2 rounded-pill">Trainer Enabled</span>
                            @else
                                <span class="badge badge-light px-3 py-2 rounded-pill">Learner Profile</span>
                            @endif
                        </div>
                        <p class="text-muted mb-2">{{ $user->designation->name ?? 'Designation not assigned' }}</p>
                        <div class="user-profile-meta">
                            <span><i class="mdi mdi-badge-account-outline"></i> {{ $user->corporate_id ?? 'N/A' }}</span>
                            <span><i class="mdi mdi-email-outline"></i> {{ $user->email ?? 'Email not available' }}</span>
                            <span><i class="mdi mdi-domain"></i> {{ $user->department->name ?? 'Department not assigned' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 mt-lg-0 d-flex flex-wrap">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mr-2 mb-2">
                        <i class="mdi mdi-pencil"></i> Edit User
                    </a>
                    <a href="{{ route('user.training.card', $user->id) }}" class="btn btn-outline-dark mr-2 mb-2">
                        <i class="mdi mdi-file-document-outline"></i> Training Card
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-light mb-2">
                        <i class="mdi mdi-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body">
                            <div class="stat-label">Roles</div>
                            <div class="stat-value">{{ $stats['roles'] }}</div>
                            <div class="stat-note">Assigned access groups</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body">
                            <div class="stat-label">Trainings</div>
                            <div class="stat-value">{{ $stats['trainings'] }}</div>
                            <div class="stat-note">Mapped modules</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 stat-card stat-card-success">
                        <div class="card-body">
                            <div class="stat-label">Passed Exams</div>
                            <div class="stat-value">{{ $stats['passed_exams'] }}</div>
                            <div class="stat-note">Recent successful attempts</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 stat-card stat-card-danger">
                        <div class="card-body">
                            <div class="stat-label">Failed Exams</div>
                            <div class="stat-value">{{ $stats['failed_exams'] }}</div>
                            <div class="stat-note">Needs follow-up</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Assigned Training Modules</h4>
                            <p class="text-muted mb-0">Current training map and enrollment status.</p>
                        </div>
                    </div>

                    @if($user->trainings->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Training</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->trainings as $training)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold">{{ $training->name }}</div>
                                                <small class="text-muted">Module ID: {{ $training->id }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $status = strtolower($training->pivot->status ?? 'enrolled');
                                                    $badgeClass = match ($status) {
                                                        'completed' => 'badge-success',
                                                        'pending' => 'badge-warning',
                                                        'in progress', 'in_progress' => 'badge-info',
                                                        default => 'badge-primary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill">
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $training->parent_id ? 'Step' : 'Program' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state-box">
                            <i class="mdi mdi-school-outline"></i>
                            <h6>No trainings assigned yet</h6>
                            <p class="text-muted mb-0">Assign modules to start tracking this user's learning path.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title mb-1">Recent Exam Activity</h4>
                    <p class="text-muted mb-4">Latest assessment outcomes for this user.</p>

                    @if($examResults->isNotEmpty())
                        <div class="timeline-list">
                            @foreach($examResults as $result)
                                <div class="timeline-item">
                                    <div class="timeline-score {{ $result->is_passed ? 'passed' : 'failed' }}">
                                        {{ round($result->percentage) }}%
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2">
                                            <h6 class="mb-1 mb-md-0">{{ $result->module->name ?? 'Module unavailable' }}</h6>
                                            <span class="text-muted small">{{ $result->created_at->format('d M Y, h:i A') }}</span>
                                        </div>
                                        <p class="mb-2 text-muted">
                                            {{ $result->correct_answers }} correct out of {{ $result->total_questions_attempted }} questions.
                                        </p>
                                        <a href="{{ route('exams.details', $result->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                            View Exam Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state-box">
                            <i class="mdi mdi-clipboard-text-outline"></i>
                            <h6>No exams attempted yet</h6>
                            <p class="text-muted mb-0">Assessment results will appear here once the user completes an exam.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Profile Details</h4>

                    <div class="detail-list">
                        <div class="detail-item">
                            <span class="detail-label">Corporate ID</span>
                            <span class="detail-value">{{ $user->corporate_id ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">System User ID</span>
                            <span class="detail-value">{{ $user->user_id ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Internal ID</span>
                            <span class="detail-value">{{ $user->internal_id ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email Address</span>
                            <span class="detail-value">{{ $user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Department</span>
                            <span class="detail-value">{{ $user->department->name ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Designation</span>
                            <span class="detail-value">{{ $user->designation->name ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Qualification</span>
                            <span class="detail-value">{{ $user->qualification ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Experience</span>
                            <span class="detail-value">
                                {{ filled($user->experience_years) ? $user->experience_years . ' years' : 'N/A' }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Roles</span>
                            <span class="detail-value">
                                @forelse($user->getRoleNames() as $role)
                                    <span class="badge badge-light border mr-1 mb-1">{{ $role }}</span>
                                @empty
                                    N/A
                                @endforelse
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Created On</span>
                            <span class="detail-value">{{ $user->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Last Updated</span>
                            <span class="detail-value">{{ $user->updated_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title mb-3">Quick Summary</h4>
                    <div class="summary-panel">
                        <div class="summary-row">
                            <span>Training-ready profile</span>
                            <strong>{{ $user->department_id && $user->designation_id ? 'Yes' : 'Pending setup' }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Trainer access</span>
                            <strong>{{ $user->is_trainer ? 'Enabled' : 'Disabled' }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Latest exam trend</span>
                            <strong>
                                @if($examResults->isEmpty())
                                    Not available
                                @elseif($examResults->first()->is_passed)
                                    Passed
                                @else
                                    Needs review
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .user-profile-page {
        --profile-ink: #123047;
        --profile-muted: #5f7487;
        --profile-surface: #ffffff;
        --profile-border: #dbe5ee;
        --profile-accent: #0f766e;
        --profile-accent-soft: rgba(15, 118, 110, 0.12);
        --profile-hero-start: #f7fbff;
        --profile-hero-end: #e7f3ee;
    }

    .user-profile-hero {
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            linear-gradient(135deg, var(--profile-hero-start), var(--profile-hero-end));
        border: 1px solid rgba(15, 118, 110, 0.08);
    }

    .user-profile-avatar {
        width: 88px;
        height: 88px;
        border-radius: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #1d4ed8);
        box-shadow: 0 20px 45px rgba(15, 118, 110, 0.22);
    }

    .user-profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        color: var(--profile-muted);
        font-size: 0.92rem;
    }

    .user-profile-meta i {
        margin-right: 0.35rem;
    }

    .stat-card {
        border: 1px solid var(--profile-border);
        background: linear-gradient(180deg, #ffffff, #f9fbfd);
    }

    .stat-card-success {
        background: linear-gradient(180deg, #ffffff, #effbf6);
    }

    .stat-card-danger {
        background: linear-gradient(180deg, #ffffff, #fff5f4);
    }

    .stat-label {
        color: var(--profile-muted);
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.6rem;
    }

    .stat-value {
        color: var(--profile-ink);
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.45rem;
    }

    .stat-note {
        color: var(--profile-muted);
        font-size: 0.92rem;
    }

    .empty-state-box {
        border: 1px dashed var(--profile-border);
        border-radius: 18px;
        padding: 2rem 1.5rem;
        text-align: center;
        background: #fbfdff;
    }

    .empty-state-box i {
        font-size: 2rem;
        color: var(--profile-accent);
        margin-bottom: 0.75rem;
    }

    .timeline-list {
        display: grid;
        gap: 1rem;
    }

    .timeline-item {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        padding: 1rem;
        border: 1px solid var(--profile-border);
        border-radius: 20px;
        background: #fcfeff;
    }

    .timeline-score {
        min-width: 78px;
        border-radius: 18px;
        padding: 0.9rem 0.75rem;
        text-align: center;
        font-weight: 700;
        font-size: 1rem;
        color: #fff;
    }

    .timeline-score.passed {
        background: linear-gradient(135deg, #0f9f6e, #22c55e);
    }

    .timeline-score.failed {
        background: linear-gradient(135deg, #dc2626, #f97316);
    }

    .timeline-content {
        flex: 1;
    }

    .detail-list {
        display: grid;
        gap: 1rem;
    }

    .detail-item {
        padding: 1rem 1.1rem;
        border: 1px solid var(--profile-border);
        border-radius: 16px;
        background: #fbfdff;
    }

    .detail-label {
        display: block;
        color: var(--profile-muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.4rem;
    }

    .detail-value {
        color: var(--profile-ink);
        font-weight: 600;
        word-break: break-word;
    }

    .summary-panel {
        display: grid;
        gap: 0.85rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        background: linear-gradient(180deg, #f8fbfd, #eef5f9);
        color: var(--profile-ink);
    }

    @media (max-width: 767.98px) {
        .user-profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            font-size: 1.6rem;
        }

        .timeline-item {
            flex-direction: column;
        }

        .timeline-score {
            min-width: 100%;
        }
    }
</style>
@endsection
