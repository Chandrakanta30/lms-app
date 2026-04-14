<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="theme-color" content="#0f172a">
  <title>@yield('title', 'Dashboard') | Vincatis LMS</title>
  <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
  <style>
    :root {
      --app-bg: #f4f7fb;
      --surface: rgba(255, 255, 255, 0.82);
      --surface-strong: #ffffff;
      --surface-dark: #0f172a;
      --surface-dark-soft: rgba(15, 23, 42, 0.82);
      --text-main: #0f172a;
      --text-soft: #64748b;
      --text-faint: #94a3b8;
      --line: rgba(148, 163, 184, 0.24);
      --line-strong: rgba(148, 163, 184, 0.36);
      --primary: #2563eb;
      --primary-strong: #1d4ed8;
      --accent: #14b8a6;
      --success: #16a34a;
      --warning: #f59e0b;
      --danger: #dc2626;
      --shadow-lg: 0 24px 60px rgba(15, 23, 42, 0.14);
      --shadow-md: 0 14px 34px rgba(15, 23, 42, 0.08);
      --radius-xl: 28px;
      --radius-lg: 22px;
      --radius-md: 16px;
      --sidebar-width: 278px;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      min-height: 100vh;
      background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 24%),
        radial-gradient(circle at top right, rgba(20, 184, 166, 0.12), transparent 20%),
        linear-gradient(180deg, #f8fbff 0%, #f4f7fb 42%, #eef3f9 100%);
      color: var(--text-main);
      font-family: "Roboto", "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    }

    .container-scroller {
      min-height: 100vh;
      background: transparent;
    }

    .page-body-wrapper {
      display: flex;
      min-height: calc(100vh - 82px);
      padding: 96px 20px 20px;
      gap: 20px;
      align-items: flex-start;
      background: transparent;
    }

    .main-panel {
      min-height: calc(100vh - 116px);
      width: 100%;
      background: transparent;
    }

    .content-wrapper {
      padding: 0;
      background: transparent;
    }

    .navbar {
      position: fixed;
      inset: 0 0 auto 0;
      z-index: 1040;
      min-height: 82px;
      padding: 14px 20px;
      background: rgba(248, 251, 255, 0.72);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.72);
      box-shadow: 0 8px 32px rgba(15, 23, 42, 0.05);
    }

    .navbar.is-scrolled {
      box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
    }

    .navbar-brand-wrapper,
    .navbar-menu-wrapper {
      width: auto;
      background: transparent;
    }

    .app-brand {
      display: inline-flex;
      align-items: center;
      gap: 14px;
      padding: 10px 14px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.74);
      border: 1px solid rgba(255, 255, 255, 0.82);
      box-shadow: var(--shadow-md);
      text-decoration: none;
    }

    .app-brand img {
      width: 42px;
      height: 42px;
      object-fit: contain;
      border-radius: 14px;
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(20, 184, 166, 0.14));
      padding: 6px;
    }

    .brand-copy {
      display: flex;
      flex-direction: column;
      line-height: 1.1;
    }

    .brand-copy strong {
      color: var(--text-main);
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: 0.01em;
    }

    .brand-copy span {
      color: var(--text-soft);
      font-size: 0.76rem;
      text-transform: uppercase;
      letter-spacing: 0.18em;
    }

    .app-navbar-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      width: 100%;
    }

    .app-nav-search {
      position: relative;
      max-width: 420px;
      width: 100%;
    }

    .app-nav-search .mdi {
      position: absolute;
      top: 50%;
      left: 18px;
      transform: translateY(-50%);
      color: var(--text-faint);
      font-size: 1.15rem;
    }

    .app-nav-search input {
      width: 100%;
      height: 52px;
      padding: 0 18px 0 48px;
      border-radius: 18px;
      border: 1px solid rgba(148, 163, 184, 0.24);
      background: rgba(255, 255, 255, 0.74);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
      transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .app-nav-search input:focus,
    .form-control:focus,
    .custom-select:focus,
    textarea:focus,
    select:focus {
      border-color: rgba(37, 99, 235, 0.4);
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
      outline: 0;
    }

    .app-utility-group {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-left: auto;
    }

    .app-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 46px;
      padding: 0 16px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.82);
      background: rgba(255, 255, 255, 0.74);
      box-shadow: var(--shadow-md);
      color: var(--text-soft);
      font-size: 0.88rem;
      font-weight: 500;
      text-decoration: none;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .app-chip:hover {
      transform: translateY(-1px);
      box-shadow: var(--shadow-lg);
      color: var(--text-main);
      text-decoration: none;
    }

    .app-chip .status-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--success));
      box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
    }

    .nav-profile {
      display: flex;
      align-items: center;
    }

    .nav-profile .nav-link {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 8px 10px 8px 8px;
      border-radius: 22px;
      border: 1px solid rgba(255, 255, 255, 0.86);
      background: rgba(255, 255, 255, 0.8);
      box-shadow: var(--shadow-md);
      color: var(--text-main);
      text-decoration: none;
    }

    .nav-profile .nav-link::after {
      margin-left: 2px;
    }

    .nav-profile img {
      width: 42px;
      height: 42px;
      object-fit: cover;
      border-radius: 16px;
      border: 2px solid rgba(37, 99, 235, 0.12);
      box-shadow: 0 8px 22px rgba(37, 99, 235, 0.18);
    }

    .nav-profile-name {
      display: flex;
      flex-direction: column;
      gap: 2px;
      line-height: 1.1;
    }

    .nav-profile-name strong {
      font-size: 0.95rem;
      font-weight: 700;
    }

    .nav-profile-name span {
      color: var(--text-soft);
      font-size: 0.78rem;
    }

    .navbar-dropdown {
      margin-top: 14px;
      border: 1px solid rgba(148, 163, 184, 0.18);
      border-radius: 18px;
      padding: 10px;
      box-shadow: var(--shadow-lg);
    }

    .navbar-dropdown .dropdown-item {
      border-radius: 12px;
      padding: 10px 12px;
      font-weight: 500;
    }

    .navbar-dropdown .dropdown-item:hover {
      background: rgba(37, 99, 235, 0.08);
    }

    .sidebar {
      position: sticky;
      top: 96px;
      width: var(--sidebar-width);
      min-width: var(--sidebar-width);
      max-height: calc(100vh - 116px);
      padding: 18px 14px;
      border-radius: 30px;
      border: 1px solid rgba(255, 255, 255, 0.88);
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.96)),
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 28%);
      box-shadow: 0 20px 44px rgba(15, 23, 42, 0.08);
      overflow-y: auto;
    }

    .sidebar::-webkit-scrollbar {
      width: 8px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: rgba(148, 163, 184, 0.24);
      border-radius: 999px;
    }

    .sidebar-search {
      position: relative;
      margin-bottom: 16px;
    }

    .sidebar-search .mdi {
      position: absolute;
      top: 50%;
      left: 16px;
      transform: translateY(-50%);
      color: var(--text-faint);
    }

    .sidebar-search input {
      width: 100%;
      height: 46px;
      padding: 0 16px 0 44px;
      color: var(--text-main);
      border-radius: 16px;
      border: 1px solid rgba(148, 163, 184, 0.18);
      background: rgba(248, 250, 252, 0.92);
    }

    .sidebar-search input::placeholder {
      color: var(--text-faint);
    }

    .sidebar-user {
      padding: 18px;
      border-radius: 24px;
      margin-bottom: 18px;
      background: linear-gradient(180deg, rgba(241, 245, 249, 0.96), rgba(255, 255, 255, 0.96));
      border: 1px solid rgba(148, 163, 184, 0.16);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
      color: var(--text-main);
    }

    .sidebar-user small,
    .sidebar-user .sidebar-caption {
      color: var(--text-soft);
    }

    .sidebar-user .user-role-pill {
      display: inline-flex;
      margin-top: 10px;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(37, 99, 235, 0.08);
      color: var(--primary);
      font-size: 0.74rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .sidebar .nav {
      flex-direction: column;
      gap: 4px;
    }

    .sidebar-section-label {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 18px 10px 8px;
      color: var(--text-faint);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.18em;
      text-transform: uppercase;
    }

    .sidebar-section-label::after {
      content: "";
      flex: 1;
      height: 1px;
      background: rgba(148, 163, 184, 0.22);
    }

    .sidebar .nav-item {
      width: 100%;
    }

    .sidebar .nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      min-height: 50px;
      margin: 0;
      padding: 11px 14px;
      border-radius: 16px;
      color: #334155;
      border: 1px solid transparent;
      transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
      text-decoration: none;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link[aria-expanded="true"] {
      color: var(--text-main);
      background: rgba(37, 99, 235, 0.06);
      border-color: rgba(37, 99, 235, 0.08);
      transform: translateX(2px);
    }

    .sidebar .nav-link.active {
      color: var(--primary-strong);
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(20, 184, 166, 0.1));
      border-color: rgba(37, 99, 235, 0.12);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.42);
    }

    .sidebar .nav-link:hover .menu-icon,
    .sidebar .nav-link[aria-expanded="true"] .menu-icon,
    .sidebar .nav-link.active .menu-icon {
      background: rgba(37, 99, 235, 0.12);
      color: var(--primary-strong);
    }

    .sidebar .menu-icon {
      width: 34px;
      height: 34px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      color: inherit;
      border-radius: 12px;
      background: rgba(148, 163, 184, 0.1);
    }

    .sidebar .menu-title {
      flex: 1;
      font-size: 0.93rem;
      font-weight: 600;
    }

    .sidebar .menu-arrow {
      color: var(--text-faint);
    }

    .sidebar .collapse .sub-menu {
      margin-top: 4px;
      margin-left: 18px;
      padding: 4px 0 6px 18px;
      border-left: 1px solid rgba(148, 163, 184, 0.18);
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .sidebar .sub-menu .nav-link {
      min-height: 40px;
      padding: 9px 12px;
      border-radius: 14px;
      color: var(--text-soft);
      background: transparent;
      border-color: transparent;
    }

    .sidebar .sub-menu .nav-link:hover,
    .sidebar .sub-menu .nav-link.active {
      color: var(--primary-strong);
      background: rgba(37, 99, 235, 0.05);
      transform: none;
      box-shadow: none;
    }

    .app-page {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .page-intro {
      position: relative;
      overflow: hidden;
      padding: 28px;
      border-radius: var(--radius-xl);
      background:
        radial-gradient(circle at top right, rgba(20, 184, 166, 0.18), transparent 26%),
        linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.94));
      color: #f8fafc;
      box-shadow: var(--shadow-lg);
    }

    .page-intro::after {
      content: "";
      position: absolute;
      inset: auto -40px -60px auto;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(37, 99, 235, 0.4), transparent 70%);
    }

    .page-intro .eyebrow {
      display: inline-flex;
      margin-bottom: 10px;
      color: rgba(226, 232, 240, 0.78);
      font-size: 0.76rem;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }

    .page-intro h1,
    .page-intro h2,
    .page-intro h3 {
      margin-bottom: 10px;
      color: #ffffff;
      font-weight: 700;
    }

    .page-intro p {
      margin-bottom: 0;
      max-width: 720px;
      color: rgba(226, 232, 240, 0.82);
      font-size: 0.98rem;
    }

    .surface-card,
    .card {
      border: 1px solid rgba(255, 255, 255, 0.78);
      border-radius: var(--radius-lg);
      background: var(--surface);
      backdrop-filter: blur(18px);
      box-shadow: var(--shadow-md);
    }

    .card .card-body,
    .surface-card .surface-card-body {
      padding: 24px;
    }

    .card-title {
      color: var(--text-main);
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .table-responsive {
      border-radius: 18px;
      border: 1px solid rgba(148, 163, 184, 0.14);
      background: rgba(255, 255, 255, 0.82);
    }

    .table {
      margin-bottom: 0;
      color: var(--text-main);
    }

    .table thead th {
      border-bottom: 1px solid rgba(148, 163, 184, 0.16);
      border-top: 0;
      padding: 16px 18px;
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--text-soft);
      background: rgba(241, 245, 249, 0.86);
    }

    .table tbody td {
      padding: 16px 18px;
      border-color: rgba(148, 163, 184, 0.12);
      vertical-align: middle;
    }

    .table-striped tbody tr:nth-of-type(odd) {
      background: rgba(248, 250, 252, 0.8);
    }

    .table tbody tr:hover {
      background: rgba(37, 99, 235, 0.04);
    }

    .btn {
      border-radius: 14px;
      min-height: 44px;
      padding: 0.72rem 1.1rem;
      border: 0;
      font-weight: 600;
      letter-spacing: 0.01em;
      box-shadow: none;
      transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 24px rgba(15, 23, 42, 0.12);
    }

    .btn:focus {
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
    }

    .btn-primary,
    .badge-primary,
    .bg-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-strong)) !important;
      color: #ffffff !important;
    }

    .btn-success,
    .badge-success,
    .bg-success {
      background: linear-gradient(135deg, #16a34a, #15803d) !important;
      color: #ffffff !important;
    }

    .btn-light {
      background: rgba(241, 245, 249, 0.88) !important;
      color: var(--text-main) !important;
      border: 1px solid rgba(148, 163, 184, 0.2) !important;
    }

    .btn-dark {
      background: linear-gradient(135deg, #1e293b, #0f172a) !important;
      color: #ffffff !important;
    }

    .btn-danger {
      background: linear-gradient(135deg, #ef4444, #dc2626) !important;
      color: #ffffff !important;
    }

    .btn-outline-primary,
    .btn-outline-success,
    .btn-outline-info,
    .btn-outline-secondary {
      background: rgba(255, 255, 255, 0.72);
      border: 1px solid rgba(148, 163, 184, 0.24);
      color: var(--text-main);
    }

    .btn-sm {
      min-height: 36px;
      padding: 0.5rem 0.85rem;
      border-radius: 12px;
    }

    .badge,
    .badge-info,
    .badge-dark,
    .badge-outline-secondary {
      border-radius: 999px;
      padding: 0.5em 0.8em;
      font-size: 0.74rem;
      font-weight: 700;
      letter-spacing: 0.04em;
    }

    .badge-info {
      background: rgba(37, 99, 235, 0.12);
      color: var(--primary);
    }

    .badge-dark {
      background: rgba(15, 23, 42, 0.1);
      color: var(--text-main);
    }

    .form-control,
    .custom-select,
    select,
    textarea {
      min-height: 48px;
      border-radius: 14px;
      border: 1px solid rgba(148, 163, 184, 0.22);
      background: rgba(255, 255, 255, 0.88);
      color: var(--text-main);
    }

    label {
      margin-bottom: 0.45rem;
      color: var(--text-main);
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 1.15rem;
    }

    .form-check-label,
    .text-muted,
    small {
      color: var(--text-soft) !important;
    }

    .pagination {
      gap: 8px;
      flex-wrap: wrap;
    }

    .page-link {
      min-width: 42px;
      height: 42px;
      border-radius: 12px !important;
      border: 1px solid rgba(148, 163, 184, 0.18);
      color: var(--text-main);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.88);
    }

    .page-item.active .page-link {
      background: linear-gradient(135deg, var(--primary), var(--primary-strong));
      border-color: transparent;
    }

    .alert {
      border: 0;
      border-radius: 18px;
      box-shadow: var(--shadow-md);
    }

    .footer {
      margin-top: 24px;
      padding: 18px 22px;
      border-radius: 22px;
      border: 1px solid rgba(255, 255, 255, 0.72);
      background: rgba(255, 255, 255, 0.76);
      box-shadow: var(--shadow-md);
    }

    .footer .text-muted {
      color: var(--text-soft) !important;
    }

    [data-ui-hidden="true"] {
      display: none !important;
    }

    @media (max-width: 1199.98px) {
      .page-body-wrapper {
        padding-left: 16px;
        padding-right: 16px;
      }

      .sidebar {
        min-width: 264px;
        width: 264px;
      }
    }

    @media (max-width: 991.98px) {
      .page-body-wrapper {
        flex-direction: column;
        padding-top: 92px;
      }

      .sidebar {
        position: fixed;
        top: 92px;
        left: 16px;
        right: 16px;
        width: auto;
        min-width: 0;
        max-height: calc(100vh - 108px);
        transform: translateY(-18px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
      }

      body.sidebar-open .sidebar {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
      }

      .app-navbar-content {
        flex-wrap: wrap;
      }

      .app-nav-search {
        order: 3;
        max-width: none;
        width: 100%;
      }

      .main-panel {
        width: 100%;
      }
    }

    @media (max-width: 767.98px) {
      .navbar {
        padding: 12px;
      }

      .page-body-wrapper {
        padding-left: 12px;
        padding-right: 12px;
      }

      .page-intro,
      .card .card-body,
      .surface-card .surface-card-body {
        padding: 18px;
      }

      .footer {
        padding: 16px;
      }

      .app-chip.status-chip {
        display: none;
      }
    }
  </style>
</head>
<body data-current-route="{{ request()->route()?->getName() ?? '' }}">
  <div class="container-scroller">
    @include('partials.nav')
    <div class="main-panel">
      @yield('content')
      <footer class="footer">
        <div class="d-sm-flex justify-content-center justify-content-sm-between align-items-center">
          <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © {{ date('Y') }} Vincatis LMS. All rights reserved.</span>
          <span class="float-none float-sm-end d-block mt-2 mt-sm-0 text-center text-muted">Built for compliance, training clarity, and faster team adoption.</span>
        </div>
      </footer>
    </div>
    </div>
  </div>

  <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
  <script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
  <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
  <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
  <script src="{{ asset('assets/js/template.js') }}"></script>
  <script src="{{ asset('assets/js/settings.js') }}"></script>
  <script src="{{ asset('assets/js/todolist.js') }}"></script>
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  <script src="{{ asset('assets/js/proBanner.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var body = document.body;
      var sidebarToggle = document.getElementById('appSidebarToggle');
      var sidebarSearch = document.getElementById('sidebarFilter');
      var navbar = document.querySelector('.navbar');
      var routeMarker = document.body.getAttribute('data-current-route');

      if (navbar) {
        var syncNavbarShadow = function () {
          navbar.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        syncNavbarShadow();
        window.addEventListener('scroll', syncNavbarShadow, { passive: true });
      }

      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
          body.classList.toggle('sidebar-open');
        });
      }

      document.addEventListener('click', function (event) {
        if (!body.classList.contains('sidebar-open')) {
          return;
        }

        var sidebar = document.getElementById('sidebar');
        var clickedToggle = sidebarToggle && sidebarToggle.contains(event.target);
        var clickedSidebar = sidebar && sidebar.contains(event.target);

        if (!clickedToggle && !clickedSidebar && window.innerWidth < 992) {
          body.classList.remove('sidebar-open');
        }
      });

      if (sidebarSearch) {
        sidebarSearch.addEventListener('input', function () {
          var query = this.value.trim().toLowerCase();
          document.querySelectorAll('#sidebar [data-nav-item]').forEach(function (item) {
            var text = (item.getAttribute('data-nav-text') || '').toLowerCase();
            item.setAttribute('data-ui-hidden', query && !text.includes(query) ? 'true' : 'false');
          });
        });
      }

      if (routeMarker) {
        document.querySelectorAll('#sidebar [data-route-match]').forEach(function (link) {
          var patterns = link.getAttribute('data-route-match').split('|');
          var isMatch = patterns.some(function (pattern) {
            if (pattern.endsWith('*')) {
              return routeMarker.indexOf(pattern.slice(0, -1)) === 0;
            }
            return routeMarker === pattern;
          });

          if (!isMatch) {
            return;
          }

          link.classList.add('active');
          var collapsePane = link.closest('.collapse');
          if (collapsePane) {
            collapsePane.classList.add('show');
            var trigger = document.querySelector('[data-bs-toggle="collapse"][href="#' + collapsePane.id + '"], [data-toggle="collapse"][href="#' + collapsePane.id + '"]');
            if (trigger) {
              trigger.classList.add('active');
              trigger.setAttribute('aria-expanded', 'true');
            }
          }
        });
      }
    });
  </script>
  @stack('scripts')
</body>
</html>
