@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="container">
        {{-- Print Action --}}
        <div class="d-print-none mb-3 text-right">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="mdi mdi-printer"></i> Print {{ $trainingProgram->name }}
            </button>
        </div>

        <div class="card p-5 border-dark shadow-none">
            <div class="card-body">
                <h2 class="text-center mb-4 text-uppercase"><u>{{ $trainingProgram->name }} Record</u></h2>

                {{-- Header Table --}}
                <table class="table table-bordered border-dark mb-4">
                    <tr>
                        <td width="25%"><strong>Employee Name:</strong></td>
                        <td width="35%">{{ $user->name }}</td>
                        <td width="20%"><strong>EMP Code:</strong></td>
                        <td>{{ $user->emp_code ?? '________' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td>{{ $user->department->name ?? '________' }}</td>
                        <td><strong>Designation:</strong></td>
                        <td>{{ $user->designation->name ?? '________' }}</td>
                    </tr>
                </table>

                {{-- Loop through the Steps of the Training --}}
                @foreach($trainingProgram->steps as $step)
                    @php 
                        // Check if user has completed this specific step
                        $log = $userLogs->where('id', $step->id)->first(); 
                    @endphp

                    <div class="step-container mt-4 p-3 border border-secondary">
                        <h5 class="font-weight-bold">Step {{ $step->step_number }}: {{ $step->name }}</h5>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Interacted Person:</strong> 
                                    {{ $log ? $log->pivot->interacted_person : '___________________________' }}
                                </p>
                                <p><strong>Designation:</strong> 
                                    {{ $log ? $log->pivot->designation : '___________________________' }}
                                </p>
                                <p><strong>Comments:</strong></p>
                                <div class="p-2 border bg-light" style="min-height: 60px;">
                                    {{ $log ? $log->pivot->comments : '' }}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-7">
                                @if($log)
                                    <small class="text-success">System Verified on: {{ \Carbon\Carbon::parse($log->pivot->completed_at)->format('d-M-Y H:i') }}</small>
                                @endif
                            </div>
                            <div class="col-5 text-right">
                                <p class="mb-0">__________________________</p>
                                <strong>Sign & Date</strong>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Footer Signatures --}}
                <div class="row mt-5 pt-4">
                    <div class="col-6 text-center">
                        <p>__________________________</p>
                        <strong>Trainee Signature</strong>
                    </div>
                    <div class="col-6 text-center">
                        <p>__________________________</p>
                        <strong>Authorized Signatory (HR)</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dark { border: 2px solid #000 !important; }
    .step-container { page-break-inside: avoid; } /* Prevents splitting a step across two pages */
    
    @media print {
        body { background: white; }
        .content-wrapper { padding: 0; margin: 0; }
        .sidebar, .navbar, .footer, .d-print-none { display: none !important; }
        .card { border: none !important; }
    }
</style>
@endsection