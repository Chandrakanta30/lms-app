@extends('partials.app')

@section('title', 'Dashboard')

@php
    $user      = auth()->user();
    $firstName = strtok($user->name ?? 'Team', ' ');
    $hour      = now()->hour;
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
@endphp

@section('content')
<div class="content-wrapper">
  <div class="app-page">

    {{-- ── PAGE HEADER ─────────────────────────────────────────── --}}
    <section class="page-intro mb-4">
      <span class="eyebrow">
        @if($isAdmin) Admin Workspace
        @elseif($isTrainer) Trainer Workspace
        @elseif($isReviewer) Reviewer Workspace
        @elseif($isApprover) Approver Workspace
        @else My Training Dashboard
        @endif
      </span>
      <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
        <div>
          <h1 class="mb-1">{{ $greeting }}, {{ $firstName }}.</h1>
          <p class="mb-0">
            @if($isAdmin)
              Here's the live picture of your training operations — users, active programmes, and items waiting on action.
            @elseif($isTrainer)
              Your assigned trainings, pending acceptance requests, and upcoming sessions are below.
            @elseif($isReviewer)
              Trainings submitted for review are listed below — open any one to review and update its status.
            @elseif($isApprover)
              Trainings marked as reviewed are ready for your approval. Open any one to take action.
            @else
              Your active training assignments and next steps are shown below.
            @endif
          </p>
        </div>
        <div class="d-flex flex-wrap" style="gap:8px;">
          @foreach($user->getRoleNames() as $role)
            <span class="badge" style="background:rgba(255,255,255,0.14);color:#fff;font-size:0.78rem;padding:6px 12px;">{{ $role }}</span>
          @endforeach
        </div>
      </div>
    </section>


    {{-- ═══════════════════════════════════════════════════════════
         ADMIN DASHBOARD
    ═══════════════════════════════════════════════════════════════ --}}
    @if($isAdmin)

      {{-- Stats row --}}
      <section class="row mb-4">
        <div class="col-6 col-xl-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(37,99,235,0.08);border-color:rgba(37,99,235,0.14);">
                <i class="mdi mdi-account-group-outline" style="font-size:1.4rem;color:#2563eb;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Total Users</div>
                <h3 class="mb-0 fw-bold">{{ $totalUsers }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(20,184,166,0.08);border-color:rgba(20,184,166,0.14);">
                <i class="mdi mdi-school-outline" style="font-size:1.4rem;color:#0d9488;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Trainers</div>
                <h3 class="mb-0 fw-bold">{{ $totalTrainers }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.14);">
                <i class="mdi mdi-book-check-outline" style="font-size:1.4rem;color:#16a34a;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Active Trainings</div>
                <h3 class="mb-0 fw-bold">{{ $activeTrainings }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.14);">
                <i class="mdi mdi-clock-outline" style="font-size:1.4rem;color:#d97706;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">In Setup</div>
                <h3 class="mb-0 fw-bold">{{ $setupTrainings }}</h3>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Exam stats strip --}}
      <section class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body d-flex flex-wrap align-items-center" style="gap:32px;">
              <div>
                <div class="text-muted mb-1" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Total Exam Attempts</div>
                <h4 class="mb-0 fw-bold">{{ $examStats['total'] }}</h4>
              </div>
              <div style="width:1px;height:36px;background:rgba(15,23,42,0.1);"></div>
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                <div>
                  <div class="text-muted" style="font-size:0.74rem;">Passed</div>
                  <strong>{{ $examStats['passed'] }}</strong>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                <div>
                  <div class="text-muted" style="font-size:0.74rem;">Failed</div>
                  <strong>{{ $examStats['failed'] }}</strong>
                </div>
              </div>
              @if($examStats['total'] > 0)
                <div class="ms-auto">
                  <div class="text-muted mb-1" style="font-size:0.74rem;">Pass Rate</div>
                  @php $passRate = round(($examStats['passed'] / $examStats['total']) * 100) @endphp
                  <div class="d-flex align-items-center gap-2">
                    <div style="width:120px;height:6px;border-radius:99px;background:rgba(15,23,42,0.08);">
                      <div style="width:{{ $passRate }}%;height:100%;border-radius:99px;background:{{ $passRate >= 80 ? '#16a34a' : ($passRate >= 60 ? '#d97706' : '#dc2626') }};"></div>
                    </div>
                    <strong style="font-size:0.9rem;">{{ $passRate }}%</strong>
                  </div>
                </div>
              @endif
              <a href="{{ route('admin.exams.logs') }}" class="btn btn-sm btn-outline-primary ms-auto">View All Logs</a>
            </div>
          </div>
        </div>
      </section>

      {{-- Needs your attention --}}
      <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">Needs your attention</h5>
      <section class="row mb-4">

        {{-- Pending review --}}
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
              <div class="d-flex align-items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:#d97706;display:inline-block;"></span>
                <h6 class="mb-0 fw-semibold">Awaiting Review</h6>
              </div>
              <span class="badge bg-warning text-dark">{{ $inreviewTrainings->count() }}</span>
            </div>
            <div class="card-body p-0">
              @forelse($inreviewTrainings as $t)
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div>
                    <div class="fw-medium" style="font-size:0.9rem;">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ ucfirst(str_replace('_', ' ', $t->training_type)) }} &middot; {{ $t->created_at->format('d M Y') }}</div>
                  </div>
                  <a href="{{ route('trainings.edit', $t->id) }}" class="btn btn-sm btn-outline-warning" style="font-size:0.78rem;">Review</a>
                </div>
              @empty
                <div class="px-4 py-4 text-muted text-center" style="font-size:0.88rem;">No trainings awaiting review.</div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Pending approval --}}
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
              <div class="d-flex align-items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:#7c3aed;display:inline-block;"></span>
                <h6 class="mb-0 fw-semibold">Awaiting Approval</h6>
              </div>
              <span class="badge bg-label-primary">{{ $reviewedTrainings->count() }}</span>
            </div>
            <div class="card-body p-0">
              @forelse($reviewedTrainings as $t)
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div>
                    <div class="fw-medium" style="font-size:0.9rem;">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ ucfirst(str_replace('_', ' ', $t->training_type)) }} &middot; {{ $t->created_at->format('d M Y') }}</div>
                  </div>
                  <a href="{{ route('trainings.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.78rem;">Approve</a>
                </div>
              @empty
                <div class="px-4 py-4 text-muted text-center" style="font-size:0.88rem;">No trainings awaiting approval.</div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Pending trainer acceptances --}}
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
              <div class="d-flex align-items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                <h6 class="mb-0 fw-semibold">Trainer Acceptances Pending</h6>
              </div>
              <span class="badge bg-danger">{{ $pendingAcceptanceModules->count() }}</span>
            </div>
            <div class="card-body p-0">
              @forelse($pendingAcceptanceModules as $module)
                <div class="px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div class="fw-medium mb-1" style="font-size:0.9rem;">{{ $module->name }}</div>
                  <div class="d-flex flex-wrap" style="gap:6px;">
                    @foreach($module->trainers as $trainer)
                      <span class="badge badge-outline-secondary">{{ $trainer->name }}</span>
                    @endforeach
                  </div>
                </div>
              @empty
                <div class="px-4 py-4 text-muted text-center" style="font-size:0.88rem;">All trainers have accepted.</div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Sessions pending approval --}}
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
              <div class="d-flex align-items-center gap-2">
                <span style="width:8px;height:8px;border-radius:50%;background:#0891b2;display:inline-block;"></span>
                <h6 class="mb-0 fw-semibold">Sessions Pending Sign-off</h6>
              </div>
              <span class="badge bg-info">{{ $pendingSessions->count() }}</span>
            </div>
            <div class="card-body p-0">
              @forelse($pendingSessions as $session)
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div>
                    <div class="fw-medium" style="font-size:0.9rem;">{{ $session->trainee->name ?? '—' }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ $session->topic }} &middot; {{ \Carbon\Carbon::parse($session->training_date)->format('d M Y') }}</div>
                  </div>
                  <a href="{{ route('sessions.index') }}" class="btn btn-sm btn-outline-info" style="font-size:0.78rem;">Sign off</a>
                </div>
              @empty
                <div class="px-4 py-4 text-muted text-center" style="font-size:0.88rem;">No sessions pending approval.</div>
              @endforelse
            </div>
          </div>
        </div>

      </section>

      {{-- Recent active trainings --}}
      <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">Active Training Programmes</h5>
      <section class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table mb-0" style="font-size:0.88rem;">
                  <thead style="background:rgba(15,23,42,0.03);">
                    <tr>
                      <th class="px-4 py-3 fw-semibold">Training</th>
                      <th class="py-3 fw-semibold">Type</th>
                      <th class="py-3 fw-semibold">Status</th>
                      <th class="py-3 fw-semibold">Trainees</th>
                      <th class="py-3 fw-semibold">Dates</th>
                      <th class="py-3 fw-semibold"></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recentTrainings as $t)
                      <tr style="border-top:1px solid rgba(15,23,42,0.06);">
                        <td class="px-4 py-3 fw-medium">{{ $t->name }}</td>
                        <td class="py-3">
                          <span class="badge {{ $t->training_type === 'classroom' ? 'bg-label-primary' : 'bg-label-info' }}">
                            {{ $t->training_type === 'classroom' ? 'Classroom' : 'Self Training' }}
                          </span>
                        </td>
                        <td class="py-3">
                          @php
                            $statusColor = ['created'=>'secondary','inreview'=>'warning','reviewed'=>'info','approved'=>'success'][$t->status] ?? 'secondary';
                          @endphp
                          <span class="badge bg-{{ $statusColor }}">{{ ucfirst($t->status) }}</span>
                        </td>
                        <td class="py-3">{{ $t->trainees_count }}</td>
                        <td class="py-3 text-muted" style="font-size:0.8rem;">
                          {{ $t->start_date ? \Carbon\Carbon::parse($t->start_date)->format('d M') : '—' }}
                          @if($t->end_date) – {{ \Carbon\Carbon::parse($t->end_date)->format('d M Y') }} @endif
                        </td>
                        <td class="py-3">
                          <a href="{{ route('trainings.show', $t->id) }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.78rem;">View</a>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="6" class="text-center text-muted py-4">No active trainings yet.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-2">
              <a href="{{ route('created-training-setup') }}" class="btn btn-sm btn-outline-primary">View all active trainings</a>
            </div>
          </div>
        </div>
      </section>


    {{-- ═══════════════════════════════════════════════════════════
         TRAINER DASHBOARD
    ═══════════════════════════════════════════════════════════════ --}}
    @elseif($isTrainer)

      {{-- Stats row --}}
      <section class="row mb-4">
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.14);">
                <i class="mdi mdi-book-check-outline" style="font-size:1.4rem;color:#16a34a;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Accepted</div>
                <h3 class="mb-0 fw-bold">{{ $acceptedCount }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.14);">
                <i class="mdi mdi-clock-alert-outline" style="font-size:1.4rem;color:#d97706;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Pending</div>
                <h3 class="mb-0 fw-bold">{{ $pendingCount }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(37,99,235,0.08);border-color:rgba(37,99,235,0.14);">
                <i class="mdi mdi-account-multiple-outline" style="font-size:1.4rem;color:#2563eb;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Trainees</div>
                <h3 class="mb-0 fw-bold">{{ $totalTrainees }}</h3>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Pending acceptances --}}
      @if($pendingAcceptances->count() > 0)
        <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">Action required</h5>
        <section class="row mb-4">
          <div class="col-12">
            <div class="card" style="border-left:4px solid #d97706;">
              <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="mdi mdi-alert-circle-outline" style="color:#d97706;font-size:1.1rem;"></i>
                <h6 class="mb-0 fw-semibold">Training assignments awaiting your acceptance</h6>
              </div>
              <div class="card-body p-0">
                @foreach($pendingAcceptances as $t)
                  <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                    <div>
                      <div class="fw-medium">{{ $t->name }}</div>
                      <div class="text-muted" style="font-size:0.8rem;">
                        {{ ucfirst(str_replace('_', ' ', $t->training_type)) }}
                        @if($t->start_date)
                          &middot; {{ \Carbon\Carbon::parse($t->start_date)->format('d M Y') }}
                          @if($t->end_date) – {{ \Carbon\Carbon::parse($t->end_date)->format('d M Y') }} @endif
                        @endif
                      </div>
                    </div>
                    <a href="{{ route('training-list') }}" class="btn btn-sm btn-warning" style="font-size:0.8rem;">View &amp; Accept</a>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </section>
      @endif

      <section class="row mb-4">
        {{-- Upcoming training dates --}}
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header py-3">
              <h6 class="mb-0 fw-semibold">Upcoming Trainings</h6>
            </div>
            <div class="card-body p-0">
              @forelse($upcomingTrainings as $t)
                <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div style="min-width:44px;text-align:center;background:rgba(37,99,235,0.08);border-radius:10px;padding:6px 0;">
                    <div style="font-size:1.1rem;font-weight:700;color:#2563eb;line-height:1;">{{ \Carbon\Carbon::parse($t->start_date)->format('d') }}</div>
                    <div style="font-size:0.68rem;color:#2563eb;text-transform:uppercase;">{{ \Carbon\Carbon::parse($t->start_date)->format('M') }}</div>
                  </div>
                  <div>
                    <div class="fw-medium" style="font-size:0.9rem;">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ ucfirst(str_replace('_', ' ', $t->training_type)) }}</div>
                  </div>
                </div>
              @empty
                <div class="px-4 py-4 text-muted text-center">No upcoming training dates scheduled.</div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Accepted trainings with trainee counts --}}
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header py-3">
              <h6 class="mb-0 fw-semibold">Your Active Trainings</h6>
            </div>
            <div class="card-body p-0">
              @forelse($acceptedTrainings as $t)
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div>
                    <div class="fw-medium" style="font-size:0.9rem;">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ $t->trainees_count }} trainee{{ $t->trainees_count !== 1 ? 's' : '' }}</div>
                  </div>
                  <a href="{{ route('attendance', $t->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.78rem;">Attendance</a>
                </div>
              @empty
                <div class="px-4 py-4 text-muted text-center">No accepted trainings yet.</div>
              @endforelse
            </div>
          </div>
        </div>
      </section>


    {{-- ═══════════════════════════════════════════════════════════
         REVIEWER DASHBOARD
    ═══════════════════════════════════════════════════════════════ --}}
    @elseif($isReviewer)

      {{-- Stats strip --}}
      <section class="row mb-4">
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.14);">
                <i class="mdi mdi-file-clock-outline" style="font-size:1.4rem;color:#d97706;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Pending Review</div>
                <h3 class="mb-0 fw-bold">{{ $pendingCount }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(20,184,166,0.08);border-color:rgba(20,184,166,0.14);">
                <i class="mdi mdi-file-check-outline" style="font-size:1.4rem;color:#0d9488;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Reviewed</div>
                <h3 class="mb-0 fw-bold">{{ $reviewedCount }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.14);">
                <i class="mdi mdi-file-certificate-outline" style="font-size:1.4rem;color:#16a34a;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Approved</div>
                <h3 class="mb-0 fw-bold">{{ $approvedCount }}</h3>
              </div>
            </div>
          </div>
        </div>
      </section>

      <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">
        {{ $pendingCount > 0 ? 'Trainings waiting for your review' : 'Nothing pending — all clear' }}
      </h5>
      <section class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body p-0">
              @forelse($pendingReview as $t)
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div>
                    <div class="fw-medium">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:0.8rem;">
                      {{ ucfirst(str_replace('_', ' ', $t->training_type)) }}
                      &middot; Created by {{ $t->creator->name ?? '—' }}
                      &middot; {{ $t->created_at->format('d M Y') }}
                    </div>
                  </div>
                  <a href="{{ route('trainings.edit', $t->id) }}" class="btn btn-sm btn-warning" style="font-size:0.8rem;">Open &amp; Review</a>
                </div>
              @empty
                <div class="px-4 py-5 text-center text-muted">
                  <i class="mdi mdi-check-circle-outline" style="font-size:2rem;color:#16a34a;display:block;margin-bottom:8px;"></i>
                  No trainings currently require your review.
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </section>


    {{-- ═══════════════════════════════════════════════════════════
         APPROVER DASHBOARD
    ═══════════════════════════════════════════════════════════════ --}}
    @elseif($isApprover)

      <section class="row mb-4">
        <div class="col-6 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(124,58,237,0.08);border-color:rgba(124,58,237,0.14);">
                <i class="mdi mdi-file-clock-outline" style="font-size:1.4rem;color:#7c3aed;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Awaiting Approval</div>
                <h3 class="mb-0 fw-bold">{{ $pendingCount }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.14);">
                <i class="mdi mdi-file-certificate-outline" style="font-size:1.4rem;color:#16a34a;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Approved Total</div>
                <h3 class="mb-0 fw-bold">{{ $approvedCount }}</h3>
              </div>
            </div>
          </div>
        </div>
      </section>

      <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">
        {{ $pendingCount > 0 ? 'Trainings waiting for your approval' : 'Nothing pending — all clear' }}
      </h5>
      <section class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body p-0">
              @forelse($pendingApproval as $t)
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                  <div>
                    <div class="fw-medium">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:0.8rem;">
                      {{ ucfirst(str_replace('_', ' ', $t->training_type)) }}
                      &middot; Created by {{ $t->creator->name ?? '—' }}
                      &middot; {{ $t->created_at->format('d M Y') }}
                    </div>
                  </div>
                  <a href="{{ route('trainings.edit', $t->id) }}" class="btn btn-sm btn-primary" style="font-size:0.8rem;">Open &amp; Approve</a>
                </div>
              @empty
                <div class="px-4 py-5 text-center text-muted">
                  <i class="mdi mdi-check-circle-outline" style="font-size:2rem;color:#16a34a;display:block;margin-bottom:8px;"></i>
                  No trainings currently require your approval.
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </section>


    {{-- ═══════════════════════════════════════════════════════════
         TRAINEE / EMPLOYEE DASHBOARD
    ═══════════════════════════════════════════════════════════════ --}}
    @else

      {{-- Stats row --}}
      <section class="row mb-4">
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(37,99,235,0.08);border-color:rgba(37,99,235,0.14);">
                <i class="mdi mdi-book-open-outline" style="font-size:1.4rem;color:#2563eb;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Enrolled</div>
                <h3 class="mb-0 fw-bold">{{ $totalEnrolled }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.14);">
                <i class="mdi mdi-check-decagram-outline" style="font-size:1.4rem;color:#16a34a;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Passed</div>
                <h3 class="mb-0 fw-bold">{{ $passedCount }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="app-chip" style="min-height:44px;padding:0 14px;background:rgba(220,38,38,0.08);border-color:rgba(220,38,38,0.14);">
                <i class="mdi mdi-close-circle-outline" style="font-size:1.4rem;color:#dc2626;"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:0.74rem;text-transform:uppercase;letter-spacing:0.1em;">Failed</div>
                <h3 class="mb-0 fw-bold">{{ $failedCount }}</h3>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Pending action items --}}
      @if($pendingItems->count() > 0)
        <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">Your next steps</h5>
        <section class="row mb-4">
          @foreach($pendingItems as $item)
            @php
              $stepLabel = match($item['next_step']) {
                'read_documents' => 'Read documents first',
                'take_exam'      => 'Exam ready — take it now',
                'retake_exam'    => 'Exam failed — retake available',
                default          => 'In progress',
              };
              $stepColor = match($item['next_step']) {
                'read_documents' => '#d97706',
                'take_exam'      => '#2563eb',
                'retake_exam'    => '#dc2626',
                default          => '#64748b',
              };
              $stepIcon = match($item['next_step']) {
                'read_documents' => 'mdi-book-open-page-variant-outline',
                'take_exam'      => 'mdi-clipboard-text-outline',
                'retake_exam'    => 'mdi-reload-alert',
                default          => 'mdi-dots-horizontal-circle-outline',
              };
              $actionRoute = match($item['next_step']) {
                'read_documents' => route('exams.read', $item['training']->id),
                'take_exam'      => route('exams.take', $item['training']->id),
                'retake_exam'    => route('exams.take', $item['training']->id),
                default          => '#',
              };
              $actionLabel = match($item['next_step']) {
                'read_documents' => 'Start Reading',
                'take_exam'      => 'Take Exam',
                'retake_exam'    => 'Retake Exam',
                default          => 'Continue',
              };
            @endphp
            <div class="col-md-6 mb-3">
              <div class="card h-100" style="border-left:4px solid {{ $stepColor }};">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                      <div class="fw-semibold mb-1">{{ $item['training']->name }}</div>
                      <span style="font-size:0.78rem;color:{{ $stepColor }};font-weight:500;">
                        <i class="mdi {{ $stepIcon }}"></i> {{ $stepLabel }}
                      </span>
                    </div>
                    @if($item['latest_result'])
                      <span class="badge {{ $item['latest_result']->is_passed ? 'bg-success' : 'bg-danger' }}">
                        {{ round($item['latest_result']->percentage) }}%
                      </span>
                    @endif
                  </div>
                  {{-- Mini progress --}}
                  <div class="d-flex align-items-center gap-2 mb-3" style="font-size:0.78rem;color:#64748b;">
                    <span class="{{ $item['reading_done'] ? 'text-success' : 'text-muted' }}">
                      <i class="mdi {{ $item['reading_done'] ? 'mdi-check-circle' : 'mdi-circle-outline' }}"></i> Reading
                    </span>
                    <span style="flex:1;height:2px;background:rgba(15,23,42,0.08);border-radius:99px;"></span>
                    <span class="{{ $item['exam_passed'] ? 'text-success' : 'text-muted' }}">
                      <i class="mdi {{ $item['exam_passed'] ? 'mdi-check-circle' : 'mdi-circle-outline' }}"></i> Assessment
                    </span>
                  </div>
                  <a href="{{ $actionRoute }}" class="btn btn-sm w-100" style="background:{{ $stepColor }};color:#fff;font-size:0.82rem;">
                    {{ $actionLabel }}
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </section>
      @else
        <div class="card mb-4" style="border-left:4px solid #16a34a;">
          <div class="card-body d-flex align-items-center gap-3">
            <i class="mdi mdi-check-circle" style="font-size:2rem;color:#16a34a;"></i>
            <div>
              <div class="fw-semibold">You're all caught up!</div>
              <div class="text-muted" style="font-size:0.88rem;">No pending actions on your active trainings right now.</div>
            </div>
          </div>
        </div>
      @endif

      {{-- Recent exam results --}}
      @if($recentResults->count() > 0)
        <h5 class="mb-3 fw-semibold" style="color:#2f2b3d;">Recent exam results</h5>
        <section class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-body p-0">
                @foreach($recentResults as $result)
                  <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(15,23,42,0.06);">
                    <div>
                      <div class="fw-medium" style="font-size:0.9rem;">{{ $result->module->name ?? '—' }}</div>
                      <div class="text-muted" style="font-size:0.78rem;">{{ $result->created_at->format('d M Y, g:i A') }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                      <div style="text-align:right;">
                        <div style="font-weight:700;font-size:1rem;color:{{ $result->is_passed ? '#16a34a' : '#dc2626' }};">{{ round($result->percentage) }}%</div>
                        <div style="font-size:0.72rem;color:{{ $result->is_passed ? '#16a34a' : '#dc2626' }};">{{ $result->is_passed ? 'PASSED' : 'FAILED' }}</div>
                      </div>
                      <a href="{{ route('exams.result', $result->id) }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.78rem;">Details</a>
                    </div>
                  </div>
                @endforeach
              </div>
              <div class="card-footer py-2 d-flex justify-content-end">
                <a href="{{ route('exams.history') }}" class="btn btn-sm btn-outline-primary">Full history</a>
              </div>
            </div>
          </div>
        </section>
      @endif

    @endif

  </div>
</div>
@endsection
