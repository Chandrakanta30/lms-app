@php
  $currentRoute = request()->route()?->getName() ?? '';
  $user = auth()->user();
  $primaryRole = $user?->getRoleNames()->first() ?? 'Team Member';
@endphp

<nav class="navbar col-lg-12 col-12 d-flex flex-row">
  <div class="app-navbar-content">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-light d-lg-none" id="appSidebarToggle" type="button" aria-label="Toggle navigation">
        <i class="mdi mdi-menu"></i>
      </button>

      <a class="app-brand" href="{{ route('dashboard') }}">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Vincatis LMS logo" />
        <span class="brand-copy">
          <strong>Vincatis LMS</strong>
          <span>Training Hub</span>
        </span>
      </a>
    </div>

    <div class="app-nav-search d-none d-md-block">
      <i class="mdi mdi-magnify"></i>
      <input type="text" placeholder="Search pages, actions, or modules visually from the sidebar" aria-label="Search navigation">
    </div>

    <div class="app-utility-group">
      <div class="app-chip status-chip">
        <span class="status-dot"></span>
        Compliance workspace online
      </div>

      <a class="app-chip d-none d-sm-inline-flex" href="{{ route('dashboard') }}">
        <i class="mdi mdi-view-dashboard-outline"></i>
        Overview
      </a>

      <div class="dropdown nav-profile">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
          <img src="{{ $user->profile_photo_url ?? asset('assets/images/faces/face5.jpg') }}" alt="profile" />
          <span class="nav-profile-name">
            <strong>{{ $user->name }}</strong>
            <span>{{ $primaryRole }}</span>
          </span>
        </a>

        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
          <a class="dropdown-item" href="{{ route('dashboard') }}">
            <i class="mdi mdi-view-dashboard-outline text-primary"></i>
            Dashboard
          </a>
          <a class="dropdown-item" href="{{ route('system.update') }}">
            <i class="mdi mdi-refresh text-primary"></i>
            Refresh system
          </a>
          <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display: none;">
            @csrf
          </form>
          <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="mdi mdi-logout text-primary"></i>
            Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid page-body-wrapper">
  <nav class="sidebar sidebar-offcanvas" id="sidebar">
    <div class="sidebar-search">
      <i class="mdi mdi-magnify"></i>
      <input type="text" id="sidebarFilter" placeholder="Filter navigation">
    </div>

    <div class="sidebar-user">
      <div class="sidebar-caption mb-2">Signed in as</div>
      <div class="h5 mb-1">{{ $user->name }}</div>
      <small>{{ $user->email ?? 'Secure workspace access' }}</small>
      <div class="user-role-pill">{{ $primaryRole }}</div>
    </div>

    <ul class="nav">
      <li class="sidebar-section-label">Overview</li>
      <li class="nav-item" data-nav-item data-nav-text="dashboard home overview analytics">
        <a class="nav-link" href="{{ route('dashboard') }}" data-route-match="dashboard">
          <i class="mdi mdi-view-dashboard-outline menu-icon"></i>
          <span class="menu-title">Dashboard</span>
        </a>
      </li>

      @canany(['user-create', 'user-list', 'trainer-list'])
        <li class="sidebar-section-label">People</li>
        <li class="nav-item" data-nav-item data-nav-text="users people trainers employees roles">
          <a class="nav-link" data-bs-toggle="collapse" data-toggle="collapse" href="#nav-users" aria-expanded="false" aria-controls="nav-users">
            <i class="mdi mdi-account-group-outline menu-icon"></i>
            <span class="menu-title">Users</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="nav-users">
            <ul class="nav flex-column sub-menu">
              @can('user-create')
                <li class="nav-item" data-nav-item data-nav-text="create user employee add">
                  <a class="nav-link" href="{{ route('users.create') }}" data-route-match="users.create">Create user</a>
                </li>
              @endcan
              @can('user-list')
                <li class="nav-item" data-nav-item data-nav-text="list users manage people">
                  <a class="nav-link" href="{{ route('users.index') }}" data-route-match="users.index|users.edit">List users</a>
                </li>
              @endcan
              @can('trainer-list')
                <li class="nav-item" data-nav-item data-nav-text="trainers faculty">
                  <a class="nav-link" href="{{ route('masters.trainers') }}" data-route-match="masters.trainers">List trainers</a>
                </li>
              @endcan
            </ul>
          </div>
        </li>
      @endcanany

      @canany(['role-create', 'role-list', 'permission-create', 'permission-list'])
        <li class="sidebar-section-label">Access</li>
      @endcanany

      @canany(['role-create', 'role-list'])
        <li class="nav-item" data-nav-item data-nav-text="roles permissions access">
          <a class="nav-link" data-bs-toggle="collapse" data-toggle="collapse" href="#nav-roles" aria-expanded="false" aria-controls="nav-roles">
            <i class="mdi mdi-shield-account-outline menu-icon"></i>
            <span class="menu-title">Roles</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="nav-roles">
            <ul class="nav flex-column sub-menu">
              @can('role-create')
                <li class="nav-item" data-nav-item data-nav-text="create role">
                  <a class="nav-link" href="{{ route('roles.create') }}" data-route-match="roles.create">Create role</a>
                </li>
              @endcan
              @can('role-list')
                <li class="nav-item" data-nav-item data-nav-text="list roles">
                  <a class="nav-link" href="{{ route('roles.index') }}" data-route-match="roles.index|roles.edit">List roles</a>
                </li>
              @endcan
            </ul>
          </div>
        </li>
      @endcanany

      @canany(['permission-create', 'permission-list'])
        <li class="nav-item" data-nav-item data-nav-text="permissions access policies">
          <a class="nav-link" data-bs-toggle="collapse" data-toggle="collapse" href="#nav-permissions" aria-expanded="false" aria-controls="nav-permissions">
            <i class="mdi mdi-key-chain-variant menu-icon"></i>
            <span class="menu-title">Permissions</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="nav-permissions">
            <ul class="nav flex-column sub-menu">
              @can('permission-create')
                <li class="nav-item" data-nav-item data-nav-text="create permission">
                  <a class="nav-link" href="{{ route('permissions.create') }}" data-route-match="permissions.create">Create permission</a>
                </li>
              @endcan
              @can('permission-list')
                <li class="nav-item" data-nav-item data-nav-text="list permissions">
                  <a class="nav-link" href="{{ route('permissions.index') }}" data-route-match="permissions.index">List permissions</a>
                </li>
              @endcan
            </ul>
          </div>
        </li>
      @endcanany

      @canany(['master-list', 'training-create', 'training-list', 'session-list'])
        <li class="sidebar-section-label">Training</li>
      @endcanany

      @can('master-list')
        <li class="nav-item" data-nav-item data-nav-text="masters departments designation">
          <a class="nav-link" href="{{ route('masters.index') }}" data-route-match="masters.index">
            <i class="mdi mdi-database-cog-outline menu-icon"></i>
            <span class="menu-title">Masters</span>
          </a>
        </li>
      @endcan


     
      

      @canany(['training-create', 'training-list'])
        <li class="nav-item" data-nav-item data-nav-text="training setup module modules programs">
          <a class="nav-link" data-bs-toggle="collapse" data-toggle="collapse" href="#nav-training" aria-expanded="false" aria-controls="nav-training">
            <i class="mdi mdi-book-open-page-variant-outline menu-icon"></i>
            <span class="menu-title">Training setup</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="nav-training">
            <ul class="nav flex-column sub-menu">
              @can('training-create')
                <li class="nav-item" data-nav-item data-nav-text="create training module">
                  <a class="nav-link" href="{{ route('trainings.create') }}" data-route-match="trainings.create">Create training</a>
                </li>
              @endcan
              @can('training-list')
                <li class="nav-item" data-nav-item data-nav-text="list training modules">
                  <a class="nav-link" href="{{ route('trainings.index') }}" data-route-match="trainings.index|trainings.edit|manage-trainers|manage-users|questions.*">List training</a>
                </li>
              @endcan
            </ul>
          </div>
        </li>
      @endcanany

      @can('session-list')
        <li class="nav-item" data-nav-item data-nav-text="training register log book sessions">
          <a class="nav-link" href="{{ route('sessions.index') }}" data-route-match="sessions.index">
            <i class="mdi mdi-clipboard-text-clock-outline menu-icon"></i>
            <span class="menu-title">Training register</span>
          </a>
        </li>
      @endcan

      <li class="sidebar-section-label">Assessment</li>
      <li class="nav-item" data-nav-item data-nav-text="schedule exams results history admin logs">
        <a class="nav-link" data-bs-toggle="collapse" data-toggle="collapse" href="#nav-schedule" aria-expanded="false" aria-controls="nav-schedule">
          <i class="mdi mdi-calendar-check-outline menu-icon"></i>
          <span class="menu-title">Training schedule</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="nav-schedule">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item" data-nav-item data-nav-text="training schedule list exam list">
              <a class="nav-link" href="{{ route('exam.list') }}" data-route-match="exam.list">Schedule list</a>
            </li>
            @can('result-history')
            <li class="nav-item" data-nav-item data-nav-text="results history">
              <a class="nav-link" href="{{ route('exams.history') }}" data-route-match="exams.history">Results history</a>
            </li>
            @endcan

            @can('admin-logs')
            <li class="nav-item" data-nav-item data-nav-text="admin logs">
              <a class="nav-link" href="{{ route('admin.exams.logs') }}" data-route-match="admin.exams.logs|admin.exams.details">Admin logs</a>
            </li>
            @endcan



          </ul>
        </div>
      </li>

      @can('documents')
      <li class="nav-item" data-nav-item data-nav-text="documents question banks">
        <a class="nav-link" data-bs-toggle="collapse" data-toggle="collapse" href="#nav-documents" aria-expanded="false" aria-controls="nav-documents">
          <i class="mdi mdi-file-document-multiple-outline menu-icon"></i>
          <span class="menu-title">Documents</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="nav-documents">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item" data-nav-item data-nav-text="documents list master documents">
              <a class="nav-link" href="{{ route('master-documents.index') }}" data-route-match="master-documents.*">Documents list</a>
            </li>
          </ul>
        </div>
      </li>
      @endcan

      @can('view-exam-list')
      <li class="nav-item" data-nav-item data-nav-text="exam question banks papers">
        <a class="nav-link" href="{{ route('exam.list') }}" data-route-match="exams.take|exams.result">
          <i class="mdi mdi-clipboard-text-outline menu-icon"></i>
          <span class="menu-title">Exam workspace</span>
        </a>
      </li>
      @endcan



      @can('induction-training')
      <li class="nav-item" data-nav-item data-nav-text="Induction Training">
          <a class="nav-link" href="{{ route('user.training.index') }}" data-route-match="user.training.index">
            <i class="mdi mdi-database-cog-outline menu-icon"></i>
            <span class="menu-title">Induction Training Progress</span>
          </a>
        </li>
        @endcan


    </ul>
  </nav>
