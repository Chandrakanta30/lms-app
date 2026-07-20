<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default" data-bs-theme="light" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="theme-color" content="#7367f0">
    <title>@yield('title', 'Dashboard') | Vincatis LMS</title>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">

    <!-- Material Design Icons (Legacy) -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">

    <!-- Iconify Icons CSS -->
    <link rel="stylesheet" href="{{ asset('new-layout/css/iconify-icons.css') }}">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('new-layout/css/node-waves.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/pickr-themes.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/demo.css') }}">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('new-layout/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/katex.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('new-layout/css/app-email.css') }}">

    <!-- Helpers -->
    <script src="{{ asset('new-layout/js/helpers.js') }}"></script>
    <!-- Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <script src="{{ asset('new-layout/js/config.js') }}"></script>

    <style>
        /* Keep original body gradient background */
        body,
        .layout-wrapper,
        .layout-container,
        .layout-page,
        .content-wrapper {
            background: transparent !important;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 24%),
                radial-gradient(circle at top right, rgba(20, 184, 166, 0.12), transparent 20%),
                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 42%, #eef3f9 100%) !important;
        }

        /* Brand Styling overrides */
        .app-brand {
            display: inline-flex;
            align-items: center;
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
            color: #2f2b3d !important;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        html[data-bs-theme="dark"] .brand-copy strong {
            color: #cfcde4 !important;
        }

        /* Sidebar Filter custom logic styles */
        [data-ui-hidden="true"] {
            display: none !important;
        }

        /* app-chip styling */
        .app-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(15, 23, 42, 0.04);
            color: #4b4b4b;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .app-chip:hover {
            transform: translateY(-1px);
            background: rgba(15, 23, 42, 0.08);
            color: #000;
            text-decoration: none;
        }

        .back-button-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 16px;
        }

        .back-button {
            gap: 8px;
        }

        /* Map legacy Bootstrap badge classes to Vuexy modern label style badges */
        .badge {
            margin: 2px !important;
            padding: 0.45em 0.75em !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
        }
        .badge.badge-primary,
        .badge.bg-primary {
            background-color: rgba(115, 103, 240, 0.16) !important;
            color: #7367f0 !important;
        }
        .badge.badge-success,
        .badge.bg-success {
            background-color: rgba(40, 199, 111, 0.16) !important;
            color: #28c76f !important;
        }
        .badge.badge-danger,
        .badge.bg-danger {
            background-color: rgba(234, 84, 85, 0.16) !important;
            color: #ea5455 !important;
        }
        .badge.badge-warning,
        .badge.bg-warning {
            background-color: rgba(255, 159, 67, 0.16) !important;
            color: #ff9f43 !important;
        }
        .badge.badge-info,
        .badge.bg-info {
            background-color: rgba(0, 207, 232, 0.16) !important;
            color: #00cfe8 !important;
        }
        .badge.badge-dark,
        .badge.bg-dark {
            background-color: rgba(75, 70, 92, 0.16) !important;
            color: #4b465c !important;
        }
        .badge.badge-secondary,
        .badge.bg-secondary {
            background-color: rgba(168, 170, 174, 0.16) !important;
            color: #a8aae0 !important;
        }
        .badge.badge-outline-secondary {
            border: 1px solid rgba(168, 170, 174, 0.4) !important;
            color: #8e909a !important;
            background: transparent !important;
        }
        .badge.badge-outline-dark {
            border: 1px solid rgba(75, 70, 92, 0.4) !important;
            color: #4b465c !important;
            background: transparent !important;
        }
        .badge.badge-outline-info {
            border: 1px solid rgba(0, 207, 232, 0.4) !important;
            color: #00cfe8 !important;
            background: transparent !important;
        }

        /* Prevent nested content-wrapper double margins/padding/backgrounds */
        .content-wrapper .content-wrapper {
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
            display: block !important;
        }

        /* Fix page-intro rendering within Vuexy container */
        .page-intro {
            position: relative;
            overflow: hidden;
            padding: 28px;
            border-radius: 12px;
            background:
                radial-gradient(circle at top right, rgba(20, 184, 166, 0.18), transparent 26%),
                linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.94));
            color: #f8fafc;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            margin-bottom: 24px;
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

        /* Page specific accordion, badges and buttons */
        body[data-current-route="trainings.index"] .custom-accordion .card-header,
        body[data-current-route="created-training-setup"] .custom-accordion .card-header,
        body[data-current-route="created-annual-training"] .custom-accordion .card-header,
        body[data-current-route="annual-training"] .custom-accordion .card-header,
        body[data-current-route="annual-training.programs"] .custom-accordion .card-header,
        body[data-current-route="created-annual-training.programs"] .custom-accordion .card-header {
            border-bottom: 0;
            transition: background 0.3s;
        }

        body[data-current-route="trainings.index"] .custom-accordion .card-header:hover,
        body[data-current-route="created-training-setup"] .custom-accordion .card-header:hover,
        body[data-current-route="created-annual-training"] .custom-accordion .card-header:hover,
        body[data-current-route="annual-training"] .custom-accordion .card-header:hover,
        body[data-current-route="annual-training.programs"] .custom-accordion .card-header:hover,
        body[data-current-route="created-annual-training.programs"] .custom-accordion .card-header:hover {
            background-color: rgba(15, 23, 42, 0.02) !important;
        }

        body[data-current-route="trainings.index"] .status-toggle-btn,
        body[data-current-route="created-training-setup"] .status-toggle-btn,
        body[data-current-route="created-annual-training"] .status-toggle-btn,
        body[data-current-route="annual-training"] .status-toggle-btn,
        body[data-current-route="annual-training.programs"] .status-toggle-btn,
        body[data-current-route="created-annual-training.programs"] .status-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 20px;
            padding: 6px 12px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        body[data-current-route="trainings.index"] .status-toggle-btn.active,
        body[data-current-route="created-training-setup"] .status-toggle-btn.active,
        body[data-current-route="created-annual-training"] .status-toggle-btn.active,
        body[data-current-route="annual-training"] .status-toggle-btn.active,
        body[data-current-route="annual-training.programs"] .status-toggle-btn.active,
        body[data-current-route="created-annual-training.programs"] .status-toggle-btn.active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        body[data-current-route="trainings.index"] .status-toggle-btn.inactive,
        body[data-current-route="created-training-setup"] .status-toggle-btn.inactive,
        body[data-current-route="created-annual-training"] .status-toggle-btn.inactive,
        body[data-current-route="annual-training"] .status-toggle-btn.inactive,
        body[data-current-route="annual-training.programs"] .status-toggle-btn.inactive,
        body[data-current-route="created-annual-training.programs"] .status-toggle-btn.inactive {
            background: rgba(148, 163, 184, 0.15);
            color: #94a3b8;
        }

        body[data-current-route="trainings.index"] .status-indicator,
        body[data-current-route="created-training-setup"] .status-indicator,
        body[data-current-route="created-annual-training"] .status-indicator,
        body[data-current-route="annual-training"] .status-indicator,
        body[data-current-route="annual-training.programs"] .status-indicator,
        body[data-current-route="created-annual-training.programs"] .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: currentColor;
        }

        body[data-current-route="trainings.index"] .status-toggle-btn:hover,
        body[data-current-route="created-training-setup"] .status-toggle-btn:hover,
        body[data-current-route="created-annual-training"] .status-toggle-btn:hover,
        body[data-current-route="annual-training"] .status-toggle-btn:hover,
        body[data-current-route="annual-training.programs"] .status-toggle-btn:hover,
        body[data-current-route="created-annual-training.programs"] .status-toggle-btn:hover {
            transform: scale(1.05);
        }

        body[data-current-route="trainings.index"] .action-buttons,
        body[data-current-route="created-training-setup"] .action-buttons,
        body[data-current-route="created-annual-training"] .action-buttons,
        body[data-current-route="annual-training"] .action-buttons,
        body[data-current-route="annual-training.programs"] .action-buttons,
        body[data-current-route="created-annual-training.programs"] .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 6px;
            overflow-x: auto;
        }

        body[data-current-route="trainings.index"] .action-buttons .btn,
        body[data-current-route="created-training-setup"] .action-buttons .btn,
        body[data-current-route="created-annual-training"] .action-buttons .btn,
        body[data-current-route="annual-training"] .action-buttons .btn,
        body[data-current-route="annual-training.programs"] .action-buttons .btn,
        body[data-current-route="created-annual-training.programs"] .action-buttons .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            padding: 0 8px;
            font-size: 11px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .fixed-card {
            height: 350px;
            display: flex;
            flex-direction: column;
        }

        .fixed-card .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .scrollable-list {
            overflow-y: auto;
            flex-grow: 1;
            max-height: 200px;
        }
    </style>
</head>

<body data-current-route="{{ request()->route()?->getName() ?? '' }}">

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu Sidebar -->
            @include('partials.sidebar')

            <!-- Layout Page -->
            <div class="layout-page">
                
                <!-- Navbar -->
                @include('partials.navbar')

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Notification System -->
                        @if (auth()->check())
                            @php
                                $notifications = auth()->user()->notifications()->where('is_read', false)->latest()->take(5)->get();
                            @endphp

                            @foreach ($notifications as $notification)
                                @if ($notification->type == 'trainer_assignment')
                                    {{-- Hidden trigger data for JS --}}
                                    <div class="d-none trainer-notification" data-id="{{ $notification->id }}"
                                        data-training="{{ $notification->training_id }}" data-title="{{ $notification->title }}"
                                        data-message="{{ $notification->message }}">
                                    </div>
                                @else
                                    <div class="alert alert-info alert-dismissible fade show mx-3 mt-3">
                                        <strong>{{ $notification->title }}</strong>
                                        <br>
                                        {{ $notification->message }}

                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST"
                                            class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-primary">OK</button>
                                        </form>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <div class="back-button-row">
                            @if (!empty($backUrl))
                                <a href="{{ $backUrl }}" class="app-chip back-button">
                                    <i class="icon-base ti tabler-arrow-left"></i>
                                    Back
                                </a>
                            @else
                                <a href="javascript:void(0);" onclick="history.back(); return false;"
                                    class="app-chip back-button">
                                    <i class="icon-base ti tabler-arrow-left"></i>
                                    Back
                                </a>
                            @endif
                        </div>

                        @yield('content')
                    </div>
                    
                    <!-- Footer -->
                    @include('partials.footer')
                    
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout Page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="{{ asset('new-layout/js/jquery.js') }}"></script>
    <script src="{{ asset('new-layout/js/popper.js') }}"></script>
    <script src="{{ asset('new-layout/js/bootstrap.js') }}"></script>
    <script src="{{ asset('new-layout/js/node-waves.js') }}"></script>
    <script src="{{ asset('new-layout/js/pickr.js') }}"></script>
    <script src="{{ asset('new-layout/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('new-layout/js/hammer.js') }}"></script>
    <script src="{{ asset('new-layout/js/i18n.js') }}"></script>
    <script src="{{ asset('new-layout/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('new-layout/js/katex.js') }}"></script>
    <script src="{{ asset('new-layout/js/quill.js') }}"></script>
    <script src="{{ asset('new-layout/js/select2.js') }}"></script>
    <script src="{{ asset('new-layout/js/notiflix.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('new-layout/js/main.js') }}"></script>

    <!-- Flash Message Handlers -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{!! addslashes(session('success')) !!}'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{!! addslashes(session('error')) !!}'
                });
            @endif
        });
    </script>

    <!-- Trainer Assignment Notifications -->
    <script>
        const routes = {
            acceptTraining: "{{ route('trainer-training.accept', ':id') }}",
            markRead: "{{ route('notifications.read', ':id') }}"
        };

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.trainer-notification').forEach(function(el) {
                const id = el.dataset.id;
                const trainingId = el.dataset.training;
                const title = el.dataset.title;
                const message = el.dataset.message;

                setTimeout(() => {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Accept Training',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#7367f0',
                        cancelButtonColor: '#808390'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(routes.acceptTraining.replace(':id', trainingId), {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => {
                                    if (response.ok) {
                                        fetch(routes.markRead.replace(':id', id), {
                                                method: 'PATCH',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            })
                                            .then(() => {
                                                Swal.fire({
                                                    title: 'Accepted!',
                                                    text: 'Training has been accepted successfully.',
                                                    icon: 'success',
                                                    timer: 1500,
                                                    showConfirmButton: false
                                                }).then(() => {
                                                    location.reload();
                                                });
                                            });
                                    } else {
                                        Swal.fire('Error', 'Failed to accept training.', 'error');
                                    }
                                })
                                .catch(() => {
                                    Swal.fire('Error', 'Something went wrong.', 'error');
                                });
                        }
                    });
                }, 500);
            });
        });
    </script>

    <!-- Sidebar Live Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sidebarSearch = document.getElementById('sidebarFilter');
            if (sidebarSearch) {
                sidebarSearch.addEventListener('input', function() {
                    var query = this.value.trim().toLowerCase();
                    document.querySelectorAll('#sidebar [data-nav-item]').forEach(function(item) {
                        var text = (item.getAttribute('data-nav-text') || '').toLowerCase();
                        item.setAttribute('data-ui-hidden', query && !text.includes(query) ? 'true' : 'false');
                    });
                });
            }
        });
    </script>

    @stack('scripts')

</body>

</html>
