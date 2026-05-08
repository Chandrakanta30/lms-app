

@php
  $currentRoute = request()->route()?->getName() ?? '';
  $user = auth()->user();
  $primaryRole = $user?->getRoleNames()->first() ?? 'Team Member';
@endphp
<nav class="navbar col-lg-12 col-12 d-flex flex-row">
    <div class="app-navbar-content">

        <div class="d-flex align-items-center gap-3">

            <button class="btn btn-light d-lg-none"
                    id="appSidebarToggle"
                    type="button"
                    aria-label="Toggle navigation">

                <i class="mdi mdi-menu"></i>
            </button>

            <a class="app-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/logo.png') }}"
                     alt="Vincatis LMS logo" />

                <span class="brand-copy">
                    <strong>Vincatis LMS</strong>
                    <span>Training Hub</span>
                </span>
            </a>
        </div>

        <div class="app-nav-search d-none d-md-block">
            <i class="mdi mdi-magnify"></i>

            <input type="text"
                   placeholder="Search pages, actions, or modules visually from the sidebar"
                   aria-label="Search navigation">
        </div>

        <div class="app-utility-group">

            <div class="app-chip status-chip">
                <span class="status-dot"></span>
                Compliance workspace online
            </div>

            <a class="app-chip d-none d-sm-inline-flex"
               href="{{ route('dashboard') }}">

                <i class="mdi mdi-view-dashboard-outline"></i>
                Overview
            </a>

            <div class="dropdown nav-profile">

                <a class="nav-link dropdown-toggle"
                   href="#"
                   data-bs-toggle="dropdown"
                   id="profileDropdown">

                    <img src="{{ $user->profile_photo_url ?? asset('assets/images/faces/face5.jpg') }}"
                         alt="profile" />

                    <span class="nav-profile-name">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $primaryRole }}</span>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end navbar-dropdown"
                     aria-labelledby="profileDropdown">

                    <a class="dropdown-item"
                       href="{{ route('dashboard') }}">

                        <i class="mdi mdi-view-dashboard-outline text-primary"></i>
                        Dashboard
                    </a>

                    <a class="dropdown-item"
                       href="{{ route('system.update') }}">

                        <i class="mdi mdi-refresh text-primary"></i>
                        Refresh system
                    </a>

                    <form method="POST"
                          action="{{ route('logout') }}"
                          id="logout-form">

                        @csrf
                    </form>

                    <a class="dropdown-item"
                       href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">

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

        {{-- Sidebar Search --}}
        <div class="sidebar-search">
            <i class="mdi mdi-magnify"></i>

            <input type="text"
                   id="sidebarFilter"
                   placeholder="Filter navigation">
        </div>

        {{-- User --}}
        <div class="sidebar-user">

            <div class="sidebar-caption mb-2">
                Signed in as
            </div>

            <div class="h5 mb-1">
                {{ $user->name }}
            </div>

            <div class="sidebar-email">
                <small>
                    {{ $user->email ?? 'Secure workspace access' }}
                </small>
            </div>

            <div class="user-role-pill">
                {{ $primaryRole }}
            </div>
        </div>

        <ul class="nav">

            {{-- DASHBOARD --}}
            <li class="sidebar-section-label">
                Overview
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">

                    <i class="mdi mdi-view-dashboard-outline menu-icon"></i>

                    <span class="menu-title">
                        Dashboard
                    </span>
                </a>
            </li>

            {{-- PEOPLE --}}
            @canany(['user-create', 'user-list', 'trainer-list'])

            <li class="sidebar-section-label">
                People
            </li>

            <li class="nav-item">

                <a class="nav-link"
                   data-bs-toggle="collapse"
                   href="#nav-users"
                   role="button"
                   aria-expanded="{{ str_contains($currentRoute, 'users') ? 'true' : 'false' }}"
                   aria-controls="nav-users">

                    <i class="mdi mdi-account-group-outline menu-icon"></i>

                    <span class="menu-title">
                        Users
                    </span>

                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ str_contains($currentRoute, 'users') ? 'show' : '' }}"
                     id="nav-users">

                    <ul class="nav flex-column sub-menu">

                      

                        @can('user-list')
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('users.index') }}">
                                Users List
                            </a>
                        </li>
                        @endcan


                        @can('user-list')
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('masters.trainers') }}">
                                Trainers List
                            </a>
                        </li>
                        @endcan

                    </ul>

                </div>
            </li>

            @endcanany

            {{-- ACCESS --}}
            @canany(['role-list', 'permission-list'])

            <li class="sidebar-section-label">
                Access
            </li>

            @can('role-list')
            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('roles.index') }}">

                    <i class="mdi mdi-shield-account-outline menu-icon"></i>

                    <span class="menu-title">
                        Roles
                    </span>
                </a>
            </li>
            @endcan

            @can('permission-list')
            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('permissions.index') }}">

                    <i class="mdi mdi-key-chain-variant menu-icon"></i>

                    <span class="menu-title">
                        Permissions
                    </span>
                </a>
            </li>
            @endcan

            @endcanany

            {{-- TRAINING --}}
            @canany(['master-list', 'training-list', 'session-list'])

            <li class="sidebar-section-label">
                Training
            </li>

            @can('master-list')
            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('masters.index') }}">

                    <i class="mdi mdi-database-cog-outline menu-icon"></i>

                    <span class="menu-title">
                        Masters
                    </span>
                </a>
            </li>
            @endcan

            @can('training-list')
            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('trainings.index') }}">

                    <i class="mdi mdi-book-open-page-variant-outline menu-icon"></i>

                    <span class="menu-title">
                        Training Setup
                    </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('training-list') }}">

                    <i class="mdi mdi-book-open-page-variant-outline menu-icon"></i>

                    <span class="menu-title">
                        Training List
                    </span>
                </a>
            </li>
            @endcan

            @can('session-list')
            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('sessions.index') }}">

                    <i class="mdi mdi-clipboard-text-clock-outline menu-icon"></i>

                    <span class="menu-title">
                        Training Register
                    </span>
                </a>
            </li>
            @endcan

            @endcanany

            {{-- ASSESSMENT --}}
            <li class="sidebar-section-label">
                Assessment
            </li>

            <li class="nav-item">

                <a class="nav-link"
                   data-bs-toggle="collapse"
                   href="#nav-schedule"
                   role="button">

                    <i class="mdi mdi-calendar-check-outline menu-icon"></i>

                    <span class="menu-title">
                        Training Schedule
                    </span>

                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse" id="nav-schedule">

                    <ul class="nav flex-column sub-menu">

                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('exam.list') }}">
                                Schedule List
                            </a>
                        </li>

                        @can('result-history')
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('exams.history') }}">
                                Results History
                            </a>
                        </li>
                        @endcan

                        @can('admin-logs')
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('admin.exams.logs') }}">
                                Admin Logs
                            </a>
                        </li>
                        @endcan

                    </ul>

                </div>
            </li>

            {{-- DOCUMENTS --}}
            @can('documents')

            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('master-documents.index') }}">

                    <i class="mdi mdi-file-document-multiple-outline menu-icon"></i>

                    <span class="menu-title">
                        Documents
                    </span>
                </a>
            </li>

            @endcan

            {{-- EXAM --}}
            @can('view-exam-list')

            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('exam.list') }}">

                    <i class="mdi mdi-clipboard-text-outline menu-icon"></i>

                    <span class="menu-title">
                        Exam Workspace
                    </span>
                </a>
            </li>

            @endcan

            {{-- INDUCTION --}}
            @can('induction-training')

            <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('user.training.index') }}">

                    <i class="mdi mdi-school-outline menu-icon"></i>

                    <span class="menu-title">
                        Induction Training Progress
                    </span>
                </a>
            </li>

            @endcan

        </ul>

    </nav>