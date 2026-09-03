@extends('partials.app')

@section('title', 'Employee Unique ID Allotment')

@section('content')
    <div class="content-wrapper">

        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">
                        Central Lab Allotment of Employee Unique ID
                    </h4>
                </div>


                <div class="employee-header">

                    {{-- Left --}}
                    <div class="employee-header-left">
                        <div class="employee-brand">
                            <div>SMS</div>
                            <small>Central Lab</small>
                        </div>
                    </div>

                    {{-- Center --}}
                    <div class="employee-header-center">
                        <h3>EMPLOYEE UNIQUE ID ALLOTMENT</h3>
                    </div>

                    {{-- Right --}}
                    <div class="employee-header-right">
                        <img src="{{ asset('assets/images/sms-logo.jpg') }}" alt="SMS Logo">
                    </div>

                </div>
                <div class="table-responsive mt-6">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Name of the Employee</th>
                                <th>Employee ID</th>
                                <th>Employment Type</th>
                                <th>Unique ID allotted</th>
                                <th>Acknowledged by<br>(Sign & Date)</th>
                                <th>Allotted by<br>(Sign & Date)</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $index => $user)
                                <tr>
                                    <td>
                                        {{ $users->firstItem() + $index }}
                                    </td>

                                    <td class="text-left">
                                        {{ $user->name }}
                                    </td>

                                    <td>
                                        {{ $user->corporate_id ?? 'N/A' }}
                                    </td>

                                    <td>
                                        @foreach ($user->getRoleNames() as $role)
                                            <label class="badge badge-info">{{ $role }}</label>
                                        @endforeach
                                    </td>

                                    <td>
                                        {{ $user->internal_id }}
                                    </td>

                                    <td>
                                        <div class="signature-cell">
                                            <div class="signature-text">{{ $user->name }}</div>
                                            <small>{{ optional($user->created_at)->format('d M Y, h:i A') }}</small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="signature-cell">
                                            <div class="signature-text">
                                                {{ $user->creator?->name ?? 'Creator not available' }}</div>
                                            <small>{{ optional($user->created_at)->format('d M Y, h:i A') }}</small>
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" name="remarks[{{ $user->id }}]" class="form-control"
                                            placeholder="Remarks">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $users->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
                <div class="mb-4">
                    <p class="text-muted mb-0">
                        Format No.: SMSCL-DQA004-F08-00
                    </p>
                </div>
            </div>
        </div>
        <style>
            .employee-header {
                display: grid !important;
                grid-template-columns: 25% 50% 25%;
                width: 100%;
                height: 100px;
                border: 1px solid #000;
                background: #fff;
                box-sizing: border-box;
            }

            .employee-header-left,
            .employee-header-center,
            .employee-header-right {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 100%;
                box-sizing: border-box;
            }

            .employee-header-left {
                border-right: 1px solid #000;
            }

            .employee-header-right {
                border-left: 1px solid #000;
            }

            .employee-brand {
                text-align: center;
                line-height: 1.1;
            }

            .employee-brand>div {
                font-size: 20px;
            }

            .employee-brand small {
                font-size: 16px;
            }

            .employee-header-center h3 {
                margin: 0 !important;
                text-align: center;
                font-size: 26px;
            }

            .employee-header-right img {
                width: 70px;
                height: 70px;
                object-fit: contain;
                display: block;
            }

            .signature-cell {
                min-width: 150px;
                min-height: 72px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2px;
            }

            .signature-text {
                font-family: 'Dancing Script', cursive;
                font-size: 1.2rem;
                color: #003366;
            }
        </style>
    @endsection
