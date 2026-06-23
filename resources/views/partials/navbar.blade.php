@php
    $user = auth()->user();
    $primaryRole = $user?->getRoleNames()->first() ?? 'Team Member';
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  
  {{-- Mobile Toggle Button --}}
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base ti tabler-menu-2 icon-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    
    {{-- Search / App Info --}}
    <div class="navbar-nav align-items-center me-auto">
      <div class="nav-item mb-0 px-2 d-none d-md-flex align-items-center gap-2">
        <span class="badge bg-label-success badge-dot me-1"></span>
        <span class="text-muted small">Compliance Workspace Online</span>
      </div>
    </div>

    <ul class="navbar-nav flex-row align-items-center">
      
      {{-- Theme/Style Switcher --}}
      <li class="nav-item dropdown me-2 me-xl-0">
        <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
          <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
          <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
          <li>
            <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light" aria-pressed="false">
              <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
              <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Dark</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system" aria-pressed="false">
              <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3" data-icon="device-desktop-analytics"></i>System</span>
            </button>
          </li>
        </ul>
      </li>

      {{-- Profile dropdown --}}
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ $user->profile_photo_url ?? asset('assets/images/faces/face5.jpg') }}" alt="user-avatar" class="rounded-circle">
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item mt-0" href="javascript:void(0)">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                  <div class="avatar avatar-online">
                    <img src="{{ $user->profile_photo_url ?? asset('assets/images/faces/face5.jpg') }}" alt="user-avatar" class="rounded-circle">
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ $user->name }}</h6>
                  <small class="text-muted">{{ $primaryRole }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('dashboard') }}">
              <i class="icon-base ti tabler-dashboard me-3 icon-md"></i>
              <span class="align-middle">Dashboard Overview</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('system.update') }}">
              <i class="icon-base ti tabler-refresh me-3 icon-md"></i>
              <span class="align-middle">Refresh System</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <form method="POST" action="{{ route('logout') }}" id="logout-form-nav" class="d-none">
              @csrf
            </form>
            <a class="dropdown-item" href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
              <i class="icon-base ti tabler-logout me-3 icon-md text-danger"></i>
              <span class="align-middle text-danger">Logout</span>
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </div>
</nav>
