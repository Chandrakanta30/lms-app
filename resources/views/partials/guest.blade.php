<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="theme-color" content="#06111e">
  <title>@yield('title', 'Welcome') | Vincatis LMS</title>
  <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
  <style>
    html, body {
      min-height: 100%;
    }

    body {
      margin: 0;
      font-family: "Roboto", "Segoe UI", "Helvetica Neue", Arial, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.26), transparent 22%),
        radial-gradient(circle at top right, rgba(20, 184, 166, 0.18), transparent 22%),
        linear-gradient(180deg, #04101c 0%, #071827 52%, #06111e 100%);
      color: #f8fafc;
    }

    .guest-shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .guest-main {
      flex: 1;
    }

    .guest-footer {
      padding: 16px 22px 24px;
      color: rgba(226, 232, 240, 0.58);
      font-size: 0.85rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="guest-shell">
    <main class="guest-main">
      @yield('content')
    </main>
    <footer class="guest-footer">
      Copyright © {{ date('Y') }} Vincatis LMS. Secure training access for regulated teams.
    </footer>
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
  @stack('scripts')
</body>
</html>
