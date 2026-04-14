@extends('partials.app')

@section('title', 'Authorized Trainers List')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-4">Internal Authorized Trainers</h3>
            
            @forelse($departments as $dept)
                <div class="card mb-4 border-left-primary shadow-sm">
                    <div class="card-body">
                        <h4 class="text-primary mb-4">
                            <strong>Department:</strong> {{ $dept->name }}
                        </h4>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Experience</th>
                                        <th>Qualification</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dept->users as $trainer)
                                        <tr>
                                            <td class="font-weight-bold">{{ $trainer->name }}</td>
                                            <td>{{ $trainer->designation->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-outline-dark">
                                                    {{ $trainer->experience_years ?? 0 }} Years
                                                </span>
                                            </td>
                                            <td>{{ $trainer->qualification ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">No authorized trainers have been assigned yet.</div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 5px solid #4B49AC !important; /* Matches typical admin dashboard primary color */
    }
    .table thead th {
        border-top: none;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
</style>
@endsection