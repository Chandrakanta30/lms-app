@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="container">

            @if (!$isEligible)

                {{-- NOT YET EARNED --}}
                <div class="card shadow-sm mx-auto" style="max-width: 720px;">
                    <div class="card-body text-center p-5">
                        <i class="mdi mdi-certificate-outline text-muted" style="font-size: 64px;"></i>

                        <h4 class="mt-3 mb-2">Training Certificate Not Available</h4>

                        <p class="text-muted mb-4">{{ $message }}</p>

                        <ul class="list-group text-left mb-4">
                            @foreach ($rows as $row)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        {{ $row['label'] }}
                                        @if ($row['total'] > 0)
                                            <small class="text-muted">({{ $row['completed'] }}/{{ $row['total'] }} steps)</small>
                                        @else
                                            <small class="text-muted">(not enrolled / no steps configured)</small>
                                        @endif
                                    </span>

                                    @if ($row['is_completed'])
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="mdi mdi-check-circle"></i> Completed
                                        </span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2">Pending</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

            @else

                {{-- Print Action --}}
                <div class="d-print-none mb-3 text-right">
                    <button onclick="window.print()" class="btn btn-dark">
                        <i class="mdi mdi-printer"></i> Print / Download Certificate
                    </button>
                </div>

                <div class="card p-4 border-dark shadow-none certificate-card">
                    <div class="card-body p-0">

                        {{-- Title Band --}}
                        <table class="table table-bordered border-dark mb-0">
                            <tr>
                                <td width="25%" class="text-center align-middle">
                                    <strong>SMS<br>Central lab</strong>
                                </td>
                                <td width="50%" class="text-center align-middle">
                                    <h4 class="mb-0 text-uppercase font-weight-bold">Training Certificate</h4>
                                </td>
                                <td width="25%" class="text-center align-middle">
                                    <img src="{{ asset('assets/images/sms-logo.jpg') }}" alt="SMS Logo"
                                        style="width: 60px; height: 60px; object-fit: contain;">
                                </td>
                            </tr>
                        </table>

                        {{-- Employee Details --}}
                        <table class="table table-bordered border-dark mb-0">
                            <tr>
                                <td width="25%"><strong>Employee Name</strong></td>
                                <td width="35%">{{ strtoupper($user->name) }}</td>
                                <td width="20%"><strong>EMP Code</strong></td>
                                <td>{{ $user->emp_code ?? ($user->corporate_id ?? '________') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Department</strong></td>
                                <td>{{ $user->department->name ?? '________' }}</td>
                                <td><strong>Designation</strong></td>
                                <td>{{ $user->designation->name ?? '________' }}</td>
                            </tr>
                        </table>

                        {{-- Declaration Rows --}}
                        <table class="table table-bordered border-dark mb-0">
                            @foreach ($rows as $row)
                                <tr style="height: 90px;">
                                    <td width="70%" class="align-middle">
                                        <strong>
                                            @if ($row['slug'] === 'functional')
                                                Functional Training was completed as<br>per Job Responsibilities
                                            @else
                                                {{ $row['label'] }} was completed
                                            @endif
                                        </strong>
                                    </td>
                                    <td class="align-middle">
                                        <strong>:</strong>
                                        <span class="ml-2 {{ $row['is_completed'] ? 'answer-marked' : 'answer-struck' }}">YES</span>
                                        /
                                        <span class="{{ $row['is_completed'] ? 'answer-struck' : 'answer-marked' }}">NO</span>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Signature Block --}}
                            <tr style="height: 170px;">
                                <td class="align-bottom">
                                    <div class="mb-1">.......................................</div>
                                    <strong>Signature of Employee</strong>
                                </td>
                                <td class="align-bottom">
                                    <div class="mb-1">.......................................</div>
                                    <strong>Signature of HOD<br>(or) Designee</strong>
                                </td>
                            </tr>

                            <tr style="height: 170px;">
                                <td colspan="2" class="align-bottom">
                                    <div class="mb-1">............................................................</div>
                                    <strong>Signature of HOD or designee- DQA</strong>
                                </td>
                            </tr>
                        </table>

                        {{-- Format Footer --}}
                        <div class="border border-dark border-top-0 px-2 py-1">
                            <small><strong>Format No.:</strong> SMSCL-DQA004-F04-00</small>
                        </div>

                        <div class="d-print-none mt-3 text-right">
                            <small class="text-muted">Generated on {{ now()->format('d M Y, h:i A') }}</small>
                        </div>

                    </div>
                </div>

            @endif

        </div>
    </div>

    <style>
        .certificate-card {
            max-width: 21cm;
            margin: 0 auto;
        }

        .border-dark {
            border: 1px solid #000 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }

        /* The template is a circle-one form: the answer that applies is marked,
           the other is struck through. */
        .answer-marked {
            font-weight: 700;
            border: 2px solid #000;
            border-radius: 50%;
            padding: 2px 8px;
        }

        .answer-struck {
            text-decoration: line-through;
            color: #777;
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
                print-color-adjust: exact;
            }
        }
    </style>
@endsection
