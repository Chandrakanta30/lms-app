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
                <h3 class="text-center mb-4">EMPLOYEE TRAINING RECORD CARD</h3>

                {{-- Employee Information Header --}}
                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-6 py-2"><strong>NAME:</strong> {{ strtoupper($user->name) }}</div>
                    <div class="col-6 py-2"><strong>EMPLOYMENT TYPE:</strong> {{ $user->employment_type ?? 'PERMANENT' }}</div>
                    
                    <div class="col-6 py-2"><strong>DESIGNATION:</strong> {{ $user->designation->name ?? 'N/A' }}</div>
                    <div class="col-6 py-2"><strong>DEPARTMENT:</strong> {{ $user->department->name ?? 'N/A' }}</div>
                    
                    <div class="col-4 py-2"><strong>EMPLOYEE CODE:</strong> {{ $user->emp_code ?? '________' }}</div>
                    <div class="col-4 py-2"><strong>CURRENT CARD NO.:</strong> {{ $user->current_card_no ?? '________' }}</div>
                    <div class="col-4 py-2"><strong>PREVIOUS CARD NO.:</strong> {{ $user->prev_card_no ?? '________' }}</div>
                </div>

                {{-- Training Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered border-dark">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">S.No.</th>
                                <th width="12%">Date</th>
                                <th width="20%">Training Register No. & Page No.</th>
                                <th width="33%">Topic</th>
                                <th width="15%">Name of the Trainer</th>
                                <th width="15%">Signature of the Trainer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalRows = 20; @endphp {{-- Pre-defined rows for a full page look --}}
                            @foreach($sessions as $index => $session)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->training_date)->format('d-m-Y') }}</td>
                                    <td>{{ $session->register_no }} / {{ $session->page_no }}</td>
                                    <td>{{ $session->topic }}</td>
                                    <td>{{ $session->trainer->name }}</td>
                                    <td class="text-center"><small><i>Digitally Signed</i></small></td>
                                </tr>
                            @endforeach

                            {{-- Fill empty rows to maintain the "Card" look if data is short --}}
                            @for($i = count($sessions); $i < $totalRows; $i++)
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

                {{-- Footer Footer Verification --}}
                <div class="row mt-5">
                    <div class="col-6">
                        <p class="mb-0">__________________________</p>
                        <strong>Signature of Employee</strong>
                    </div>
                    <div class="col-6 text-right">
                        <p class="mb-0">__________________________</p>
                        <strong>Authorized Signatory (QA/HR)</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dark { border: 1px solid #000 !important; }
    .table-bordered th, .table-bordered td { border: 1px solid #000 !important; vertical-align: middle; }
    
    @media print {
        body { background: white !important; }
        .content-wrapper { padding: 0 !important; margin: 0 !important; }
        .card { border: none !important; }
        .sidebar, .navbar, .footer, .d-print-none { display: none !important; }
        
        /* Ensure table borders appear in Chrome/Edge */
        table { border-collapse: collapse !important; }
        th, td { border: 1px solid black !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection