@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            <div class="d-print-none mb-3 text-right">
                <button onclick="window.print()" class="btn btn-dark">
                    <i class="mdi mdi-printer"></i> Print Training Card
                </button>
            </div>

            @php
                $rowsPerPage = 20;
                $sessionPages = $sessions->chunk($rowsPerPage);

                if ($sessionPages->isEmpty()) {
                    $sessionPages = collect([collect()]);
                }
            @endphp

            @forelse($sessionPages as $pageIndex => $pageSessions)
                <div class="card p-4 border-dark shadow-none training-card-page {{ $pageIndex > 0 ? 'training-card-page--continued' : '' }}"
                    style="min-height: 29.7cm; {{ $pageIndex > 0 ? 'page-break-before: always;' : '' }}">
                    <div class="card-body">
                        @if ($pageIndex === 0)
                            <div class="training-card-header mb-4 border border-dark">



                                <div class="training-card-header__cell training-card-header__cell--left">
                                    <div class="training-card-header__brand">
                                        <div class="training-card-header__brand-line">SMS</div>
                                        <div
                                            class="training-card-header__brand-line training-card-header__brand-line--small">
                                            Central Lab</div>
                                    </div>
                                </div>

                                <div class="training-card-header__cell training-card-header__cell--center">
                                    <h3 class="mb-0 text-center">STAFF TRAINING CARD</h3>
                                </div>

                                <div class="training-card-header__cell training-card-header__cell--right">
                                    <img src="{{ asset('assets/images/sms-logo.jpg') }}" alt="SMS Logo"
                                        class="training-card-header__logo">
                                </div>
                            </div>

                            <div class="row mb-4 border-bottom pb-3">
                                <div class="col-6 py-2"><strong>NAME:</strong> {{ strtoupper($user->name) }}</div>
                                <div class="col-6 py-2"><strong>EMPLOYMENT TYPE:</strong>
                                    {{ $user->employment_type ?? 'PERMANENT' }}</div>

                                <div class="col-6 py-2"><strong>DESIGNATION:</strong>
                                    {{ $user->designation->name ?? 'N/A' }}</div>
                                <div class="col-6 py-2"><strong>DEPARTMENT:</strong> {{ $user->department->name ?? 'N/A' }}
                                </div>

                                <div class="col-12 py-2"><strong>EMPLOYEE CODE:</strong>
                                    {{ $user->emp_code ?? ($user->corporate_id ?? '________') }}</div>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered border-dark mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">S.No.</th>
                                        <th width="18%">Date</th>
                                        <th width="30%">Topic</th>
                                        <th width="16%">Type of training</th>
                                        <th width="18%">Name of the Trainer</th>
                                        <th width="13%">signature of the trainer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pageSessions as $index => $session)
                                        @forelse(($session->document_names ?? ['N/A']) as $documentName)
                                            <tr>
                                                <td>{{ $pageIndex * $rowsPerPage + $index + 1 }}</td>

                                                <td>
                                                    {{ $session->start_date
                                                        ? \Carbon\Carbon::parse($session->start_date)->format('d-m-Y')
                                                        : ($session->end_date
                                                            ? \Carbon\Carbon::parse($session->end_date)->format('d-m-Y')
                                                            : 'N/A') }}
                                                </td>

                                                <td>{{ $documentName }}</td>

                                                <td>{{ $session->type_label ?? 'Regular' }}</td>

                                                <td>{{ $session->trainer_name ?? 'N/A' }}</td>

                                                <td class="text-center">
                                                    @if (($session->is_self_training ?? false) && ($session->status ?? 'pending') === 'passed')
                                                        <small><i>{{ $session->user->name ?? 'N/A' }}</i></small>
                                                    @elseif ($session->is_approved ?? false)
                                                        <small><i>{{ $session->approved_name ?? 'N/A' }}</i></small>
                                                    @else
                                                        <small><i>Pending</i></small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td>{{ $pageIndex * $rowsPerPage + $index + 1 }}</td>
                                                <td>
                                                    {{ $session->start_date
                                                        ? \Carbon\Carbon::parse($session->start_date)->format('d-m-Y')
                                                        : ($session->end_date
                                                            ? \Carbon\Carbon::parse($session->end_date)->format('d-m-Y')
                                                            : 'N/A') }}
                                                </td>
                                                <td>N/A</td>
                                                <td>{{ $session->type_label ?? 'Regular' }}</td>
                                                <td>{{ $session->trainer_name ?? 'N/A' }}</td>
                                                <td class="text-center">Pending</td>
                                            </tr>
                                        @endforelse
                                    @endforeach

                                    @for ($i = count($pageSessions); $i < $rowsPerPage; $i++)
                                        <tr style="height: 40px;">
                                            <td>{{ $pageIndex * $rowsPerPage + $i + 1 }}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        @if ($pageIndex === 0)
                            <div class="row mt-5">
                                <div class="col-6">
                                    <div><strong>Requested By:</strong> {{ auth()->user()->name ?? 'System User' }}
                                    </div>
                                    <div><strong>Signature:</strong> __________________________</div>
                                </div>
                                <div class="col-6 text-right">
                                    <div><strong>Timestamp:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @empty
                @endforelse

                @if ($sessionPages->isEmpty())
                    <div class="card p-4 border-dark shadow-none training-card-page" style="min-height: 29.7cm;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <img src="{{ asset('assets/images/sms-logo.jpg') }}" alt="SMS Logo"
                                    class="training-card-header__logo training-card-header__logo--compact">
                                <h3 class="mb-0">STAFF TRAINING CARD</h3>
                            </div>

                            <div class="row mb-4 border-bottom pb-3">
                                <div class="col-6 py-2"><strong>NAME:</strong> {{ strtoupper($user->name) }}</div>
                                <div class="col-6 py-2"><strong>EMPLOYMENT TYPE:</strong>
                                    {{ $user->employment_type ?? 'PERMANENT' }}</div>

                                <div class="col-6 py-2"><strong>DESIGNATION:</strong> {{ $user->designation->name ?? 'N/A' }}
                                </div>
                                <div class="col-6 py-2"><strong>DEPARTMENT:</strong> {{ $user->department->name ?? 'N/A' }}
                                </div>

                                <div class="col-12 py-2"><strong>EMPLOYEE CODE:</strong>
                                    {{ $user->emp_code ?? ($user->corporate_id ?? '________') }}</div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered border-dark mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="5%">S.No.</th>
                                            <th width="18%">Date</th>
                                            <th width="30%">Topic</th>
                                            <th width="16%">Type of training</th>
                                            <th width="18%">Name of the Trainer</th>
                                            <th width="13%">signature of the trainer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 0; $i < $rowsPerPage; $i++)
                                            <tr style="height: 40px;">
                                                <td>{{ $i + 1 }}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-5">
                                <div class="col-6">
                                    <div><strong>Requested By:</strong> {{ auth()->user()->name ?? 'System User' }}</div>
                                    <div><strong>Signature:</strong> __________________________</div>
                                </div>
                                <div class="col-6 text-right">
                                    <div><strong>Timestamp:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <style>
            .training-card-header {
                display: flex;
                align-items: stretch;
                width: 100%;
                min-height: 100px;
                background: #fff;
            }

            .training-card-header__cell {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 12px 16px;
                min-height: 100px;
            }

            .training-card-header__cell--left,
            .training-card-header__cell--right {
                flex: 0 0 25%;
            }

            .training-card-header__cell--center {
                flex: 0 0 50%;
            }

            .training-card-header__cell--left {
                border-right: 1px solid #000;
            }

            .training-card-header__cell--right {
                border-left: 1px solid #000;
            }

            .training-card-header__brand {
                text-align: center;
                line-height: 1.1;
            }

            .training-card-header__brand-line {
                font-size: 20px;
            }

            .training-card-header__brand-line--small {
                font-size: 16px;
            }

            .training-card-header__logo {
                width: 70px;
                height: 70px;
                object-fit: contain;
                display: block;
            }

            .training-card-header__logo--compact {
                width: 52px;
                height: 52px;
                margin-right: 14px;
            }

            .border-dark {
                border: 1px solid #000 !important;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #000 !important;
                vertical-align: middle;
            }

            @media print {
                body {
                    background: white !important;
                }

                .content-wrapper {
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .card {
                    border: none !important;
                }

                .sidebar,
                .navbar,
                .footer,
                .d-print-none {
                    display: none !important;
                }

                table {
                    border-collapse: collapse !important;
                }

                th,
                td {
                    border: 1px solid black !important;
                    -webkit-print-color-adjust: exact;
                }

                .training-card-page--continued {
                    page-break-before: always;
                }
            }
        </style>
    @endsection
