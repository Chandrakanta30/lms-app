@extends('partials.app')

@section('title', 'Dashboard')

@php
  $user = auth()->user();
  $firstName = strtok($user->name ?? 'Team', ' ');
  $hour = now()->hour;
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $roleNames = $user->getRoleNames();
  $quickActions = collect([
    ['label' => 'Create user', 'route' => 'users.create', 'icon' => 'mdi-account-plus-outline', 'description' => 'Add new learners or administrators.', 'show' => $user->can('user-create')],
    ['label' => 'Training setup', 'route' => 'trainings.index', 'icon' => 'mdi-book-open-page-variant-outline', 'description' => 'Organize modules, trainers, and enrolled users.', 'show' => $user->can('training-list')],
    ['label' => 'Register log', 'route' => 'sessions.index', 'icon' => 'mdi-clipboard-text-clock-outline', 'description' => 'Review ongoing session records and approvals.', 'show' => $user->can('session-list')],
    ['label' => 'Exam center', 'route' => 'exam.list', 'icon' => 'mdi-clipboard-check-outline', 'description' => 'Track schedules, attempts, and results.', 'show' => true],
  ])->filter(fn ($action) => $action['show'])->values();
@endphp

@section('content')
<div class="content-wrapper">
  <div class="app-page">
    <section class="page-intro">
      <span class="eyebrow">Smart training workspace</span>
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1>{{ $greeting }}, {{ $firstName }}.</h1>
          <p>Your learning operations hub is now structured for faster action, better visibility, and a calmer day-to-day workflow. Jump into the tools you use most, keep compliance activity in view, and move between modules without hunting through menus.</p>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0">
          <div class="surface-card" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.12); box-shadow: none;">
            <div class="surface-card-body">
              <div class="text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.16em; color: rgba(226,232,240,0.74);">Current access</div>
              <div class="d-flex flex-wrap" style="gap: 10px;">
                @forelse($roleNames as $role)
                  <span class="badge" style="background: rgba(255,255,255,0.12); color: #fff;">{{ $role }}</span>
                @empty
                  <span class="badge" style="background: rgba(255,255,255,0.12); color: #fff;">Workspace member</span>
                @endforelse
              </div>
              <div class="mt-3" style="color: rgba(226,232,240,0.82);">
                Last refreshed on {{ now()->format('d M Y') }}. Use the quick actions below to continue where you left off.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row">
      <div class="col-md-6 col-xl-3 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="text-uppercase text-muted mb-2" style="font-size: 0.74rem; letter-spacing: 0.14em;">Workspace</div>
                <h3 class="mb-1">Unified</h3>
              </div>
              <div class="app-chip" style="min-height: 38px; padding: 0 12px;">
                <i class="mdi mdi-layers-triple-outline"></i>
              </div>
            </div>
            <p class="mb-0 text-muted">Users, training, assessments, and compliance records are now reachable from one cleaner navigation flow.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xl-3 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="text-uppercase text-muted mb-2" style="font-size: 0.74rem; letter-spacing: 0.14em;">Navigation</div>
                <h3 class="mb-1">Faster</h3>
              </div>
              <div class="app-chip" style="min-height: 38px; padding: 0 12px;">
                <i class="mdi mdi-compass-outline"></i>
              </div>
            </div>
            <p class="mb-0 text-muted">Filter the sidebar instantly, keep active sections expanded, and move through the platform with fewer clicks.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xl-3 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="text-uppercase text-muted mb-2" style="font-size: 0.74rem; letter-spacing: 0.14em;">Interface</div>
                <h3 class="mb-1">Clearer</h3>
              </div>
              <div class="app-chip" style="min-height: 38px; padding: 0 12px;">
                <i class="mdi mdi-monitor-shimmer"></i>
              </div>
            </div>
            <p class="mb-0 text-muted">Cards, forms, tables, and controls now share one visual language for a more polished and user-friendly experience.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xl-3 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="text-uppercase text-muted mb-2" style="font-size: 0.74rem; letter-spacing: 0.14em;">Readiness</div>
                <h3 class="mb-1">Audit aware</h3>
              </div>
              <div class="app-chip" style="min-height: 38px; padding: 0 12px;">
                <i class="mdi mdi-shield-check-outline"></i>
              </div>
            </div>
            <p class="mb-0 text-muted">The workspace now surfaces the training and exam tools as a coherent operational flow instead of scattered pages.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="row">
      <div class="col-xl-8 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
              <div>
                <h4 class="card-title mb-1">Quick actions</h4>
                <p class="text-muted mb-0">Shortcuts into the flows your team likely uses every day.</p>
              </div>
            </div>

            <div class="row">
              @foreach($quickActions as $action)
                <div class="col-md-6 mb-3">
                  <a href="{{ route($action['route']) }}" class="card h-100 text-decoration-none" style="background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.9));">
                    <div class="card-body">
                      <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="app-chip" style="min-height: 40px; padding: 0 12px;">
                          <i class="mdi {{ $action['icon'] }}"></i>
                        </div>
                        <i class="mdi mdi-arrow-top-right text-muted"></i>
                      </div>
                      <h5 class="mb-2 text-dark">{{ $action['label'] }}</h5>
                      <p class="mb-0 text-muted">{{ $action['description'] }}</p>
                    </div>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-4 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title mb-3">How the new UI helps</h4>
            <div class="d-flex flex-column" style="gap: 14px;">
              <div class="p-3" style="border-radius: 16px; background: rgba(37,99,235,0.06);">
                <div class="font-weight-bold mb-1">1. Better wayfinding</div>
                <div class="text-muted">Sectioned navigation and route-aware highlights keep users oriented.</div>
              </div>
              <div class="p-3" style="border-radius: 16px; background: rgba(20,184,166,0.08);">
                <div class="font-weight-bold mb-1">2. Less visual noise</div>
                <div class="text-muted">Cleaner spacing, softer surfaces, and more legible controls reduce fatigue.</div>
              </div>
              <div class="p-3" style="border-radius: 16px; background: rgba(245,158,11,0.08);">
                <div class="font-weight-bold mb-1">3. More interaction</div>
                <div class="text-muted">Hover states, collapsible sections, live sidebar filtering, and focused action cards add responsiveness.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
