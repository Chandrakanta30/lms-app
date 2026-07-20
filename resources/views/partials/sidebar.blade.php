@php
    $currentRoute = request()->route()?->getName() ?? '';
    $user = auth()->user();
    $primaryRole = $user?->getRoleNames()->first() ?? 'Team Member';
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <a class="app-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Vincatis LMS logo" />
            <span class="brand-copy ms-2">
                <strong class="fw-bold">Vincatis LMS</strong>
                <span class="text-uppercase text-muted" style="font-size: 0.68rem; letter-spacing: 0.14em;">Training
                    Hub</span>
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    {{-- Sidebar Search --}}
    <div class="px-4 py-3 sidebar-search-wrapper">
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base ti tabler-search text-muted"></i></span>
            <input type="text" id="sidebarFilter" class="form-control" placeholder="Search...">
        </div>
    </div>

    {{-- User Info Summary --}}
    <div class="px-4 py-2 border-bottom mb-2 text-center sidebar-profile-summary">
        <div class="avatar avatar-md mx-auto mb-2">
            <img src="{{ $user->profile_photo_url ?? asset('assets/images/faces/face5.jpg') }}" alt="user-avatar"
                class="rounded-circle">
        </div>
        <h6 class="mb-0 fw-semibold text-truncate" style="max-width: 100%;">{{ $user->name }}</h6>
        <small class="text-muted d-block text-truncate" style="max-width: 100%;">{{ $user->email }}</small>
        <span class="badge bg-label-primary mt-2">{{ $primaryRole }}</span>
    </div>

    <ul class="menu-inner py-1" id="sidebar">

        {{-- OVERVIEW SECTION --}}
        <li class="menu-header small text-uppercase" data-nav-item="true" data-nav-text="Overview Dashboard">
            <span class="menu-header-text">Overview</span>
        </li>

        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-nav-item="true"
            data-nav-text="Dashboard Overview">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-smart-home"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('help.index') ? 'active' : '' }}" data-nav-item="true"
            data-nav-text="Help User Guide How To">
            <a href="{{ route('help.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-help-circle"></i>
                <div>Help / User Guide</div>
            </a>
        </li>

        {{-- PEOPLE SECTION --}}
        @canany(['user-create', 'user-list', 'trainer-list'])
            <li class="menu-header small text-uppercase" data-nav-item="true" data-nav-text="People Users Trainers">
                <span class="menu-header-text">People</span>
            </li>

            <li class="menu-item {{ str_contains($currentRoute, 'users') || str_contains($currentRoute, 'trainers') ? 'active open' : '' }}"
                data-nav-item="true" data-nav-text="Users Trainers List">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-users"></i>
                    <div>Users & Trainers</div>
                </a>
                <ul class="menu-sub">
                    @can('user-list')
                        <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="menu-link">
                                <div>Users List</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('masters.trainers') ? 'active' : '' }}">
                            <a href="{{ route('masters.trainers') }}" class="menu-link">
                                <div>Trainers List</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- ACCESS SECTION --}}
        @canany(['role-list', 'permission-list'])
            <li class="menu-header small text-uppercase" data-nav-item="true" data-nav-text="Access Roles Permissions">
                <span class="menu-header-text">Access Control</span>
            </li>

            @can('role-list')
                <li class="menu-item {{ str_contains($currentRoute, 'roles') ? 'active' : '' }}" data-nav-item="true"
                    data-nav-text="Roles Access">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-shield-lock"></i>
                        <div>Roles</div>
                    </a>
                </li>
            @endcan

            @can('permission-list')
                <li class="menu-item {{ str_contains($currentRoute, 'permissions') ? 'active' : '' }}" data-nav-item="true"
                    data-nav-text="Permissions Access">
                    <a href="{{ route('permissions.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-key"></i>
                        <div>Permissions</div>
                    </a>
                </li>
            @endcan
        @endcanany

        {{-- TRAINING SECTION --}}
        @canany(['master-list', 'training-list', 'session-list'])
            <li class="menu-header small text-uppercase" data-nav-item="true"
                data-nav-text="Training Masters Setup Calendar Register">
                <span class="menu-header-text">Training</span>
            </li>

            @can('master-list')
                <li class="menu-item {{ str_contains($currentRoute, 'masters') && !str_contains($currentRoute, 'trainers') ? 'active' : '' }}"
                    data-nav-item="true" data-nav-text="Masters Setup Department Designation Venue Section">
                    <a href="{{ route('masters.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-settings-cog"></i>
                        <div>Masters</div>
                    </a>
                </li>
            @endcan

            @can('training-list')
                <li class="menu-item {{ request()->routeIs('trainings.index') || request()->routeIs('created-training-setup') || request()->routeIs('annual-training') || request()->routeIs('created-annual-training') || request()->routeIs('training-list') || request()->routeIs('training-calendar') ? 'active open' : '' }}"
                    data-nav-item="true" data-nav-text="Training Setup Created Annual Plan Calendar List">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon icon-base ti tabler-book-2"></i>
                        <div>Training Program</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('trainings.index') ? 'active' : '' }}">
                            <a href="{{ route('trainings.index') }}" class="menu-link">
                                <div>Training Setup</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('created-training-setup') ? 'active' : '' }}">
                            <a href="{{ route('created-training-setup') }}" class="menu-link">
                                <div>Created Training Setup</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('annual-training') ? 'active' : '' }}">
                            <a href="{{ route('annual-training') }}" class="menu-link">
                                <div>Annual Plan Setup</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('created-annual-training') ? 'active' : '' }}">
                            <a href="{{ route('created-annual-training') }}" class="menu-link">
                                <div>Created Annual Plan</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('training-list') ? 'active' : '' }}">
                            <a href="{{ route('training-list') }}" class="menu-link">
                                <div>Training List</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('training-calendar') ? 'active' : '' }}">
                            <a href="{{ route('training-calendar') }}" class="menu-link">
                                <div>Training Calendar</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @can('session-list')
                <li class="menu-item {{ request()->routeIs('sessions.index') ? 'active' : '' }}" data-nav-item="true"
                    data-nav-text="Training Register Logs Session Approval">
                    <a href="{{ route('sessions.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-clipboard-text"></i>
                        <div>Training Register</div>
                    </a>
                </li>
            @endcan
        @endcanany

        {{-- ASSESSMENT SECTION --}}
        <li class="menu-header small text-uppercase" data-nav-item="true"
            data-nav-text="Assessment Exams Schedule History Logs">
            <span class="menu-header-text">Assessment</span>
        </li>

        <li class="menu-item {{ request()->routeIs('exam.list') || request()->routeIs('exams.history') || request()->routeIs('admin.exams.logs') ? 'active open' : '' }}"
            data-nav-item="true" data-nav-text="Training Schedule List Results History Admin Logs">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-checkbox"></i>
                <div>Training Schedule</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('exam.list') ? 'active' : '' }}">
                    <a href="{{ route('exam.list') }}" class="menu-link">
                        <div>Schedule List</div>
                    </a>
                </li>
                @can('result-history')
                    <li class="menu-item {{ request()->routeIs('exams.history') ? 'active' : '' }}">
                        <a href="{{ route('exams.history') }}" class="menu-link">
                            <div>Results History</div>
                        </a>
                    </li>
                @endcan
                @can('admin-logs')
                    <li class="menu-item {{ request()->routeIs('admin.exams.logs') ? 'active' : '' }}">
                        <a href="{{ route('admin.exams.logs') }}" class="menu-link">
                            <div>Admin Logs</div>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>

        {{-- DOCUMENTS --}}
        @can('documents')
            <li class="menu-item {{ str_contains($currentRoute, 'master-documents') ? 'active' : '' }}"
                data-nav-item="true" data-nav-text="Documents File Vault Controlled Documents">
                <a href="{{ route('master-documents.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-file-text"></i>
                    <div>Documents</div>
                </a>
            </li>
        @endcan

        {{-- EXAM WORKSPACE --}}
        @can('view-exam-list')
            <li class="menu-item {{ request()->routeIs('exam.list') ? 'active' : '' }}" data-nav-item="true"
                data-nav-text="Exam Workspace Take Exam">
                <a href="{{ route('exam.list') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-notebook"></i>
                    <div>Exam Workspace</div>
                </a>
            </li>
        @endcan

        {{-- INDUCTION --}}
        @can('induction-training')
            <li class="menu-item {{ request()->routeIs('user.training.index') ? 'active' : '' }}" data-nav-item="true"
                data-nav-text="Induction Training Progress Trainee Setup">
                <a href="{{ route('user.training.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-school"></i>
                    <div>Induction Progress</div>
                </a>
            </li>
        @endcan

    </ul>
</aside>
