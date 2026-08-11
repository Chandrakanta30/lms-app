@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="container">
            {{-- Print Action --}}
            <div class="d-print-none mb-3 text-right">
                <button onclick="window.print()" class="btn btn-dark">
                    <i class="mdi mdi-printer"></i> Print Training Card
                </button>
            </div>

            <div class="card p-4 border-dark shadow-none" style="min-height: 29.7cm;"> {{-- A4 Height approximation --}}
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <img src="{{ asset('assets/images/sms-logo.jpg') }}" alt="SMS Logo"
                            style="width: 52px; height: 52px; object-fit: contain; margin-right: 14px;">
                        <h3 class="mb-0">STAFF TRAINING CARD</h3>
                    </div>

                    {{-- Employee Information Header --}}
                    <div class="row mb-4 border-bottom pb-3">
                        <div class="col-6 py-2"><strong>NAME:</strong> {{ strtoupper($user->name) }}</div>
                        <div class="col-6 py-2"><strong>EMPLOYMENT TYPE:</strong>
                            {{ $user->employment_type ?? 'PERMANENT' }}</div>

                        <div class="col-6 py-2"><strong>DESIGNATION:</strong> {{ $user->designation->name ?? 'N/A' }}</div>
                        <div class="col-6 py-2"><strong>DEPARTMENT:</strong> {{ $user->department->name ?? 'N/A' }}</div>

                        <div class="col-12 py-2"><strong>EMPLOYEE CODE:</strong>
                            {{ $user->emp_code ?? ($user->corporate_id ?? '________') }}</div>
                    </div>

                    {{-- Training Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered border-dark">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">S.No.</th>
                                    <th width="30%">Training Module</th>
                                    <th width="12%">Start Date</th>
                                    <th width="12%">End Date</th>
                                    <th width="15%">Trainer</th>
                                    <th width="12%">Status</th>
                                    <th width="14%">Signature</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalRows = 20; @endphp {{-- Pre-defined rows for a full page look --}}
                                @foreach ($sessions as $index => $session)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $session->module->name ?? 'N/A' }}</td>
                                        <td>{{ $session->start_date ? \Carbon\Carbon::parse($session->start_date)->format('d-m-Y') : 'N/A' }}</td>
                                        <td>{{ $session->end_date ? \Carbon\Carbon::parse($session->end_date)->format('d-m-Y') : 'N/A' }}</td>
                                        <td>{{ $session->trainer_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $session->status_class ?? 'badge-warning' }} p-2 text-uppercase">
                                                {{ $session->status_label ?? 'Pending' }}
                                            </span>
                                            @if (!empty($session->reassignment_note))
                                                <small class="d-block mt-1 text-muted">{{ $session->reassignment_note }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if (($session->signature_session ?? null)?->is_approved)
                                                <small><i>{{ $session->signature_session?->approver?->name ?? 'N/A' }}</i></small>
                                            @else
                                                <small><i>Pending</i></small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Fill empty rows to maintain the "Card" look if data is short --}}
                                @for ($i = count($sessions); $i < $totalRows; $i++)
                                    <tr style="height: 40px;">
                                        <td>{{ $i + 1 }}</td>
                                        <td></td>
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

                    {{-- Footer Footer Verification --}}
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
        </div>
    </div>

    <style>
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

            /* Ensure table borders appear in Chrome/Edge */
            table {
                border-collapse: collapse !important;
            }

            th,
            td {
                border: 1px solid black !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endsection
