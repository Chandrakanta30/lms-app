@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="page-title text-dark">My Assessment History</h3>
            <p class="text-muted small">View your previous exam scores and compliance status.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Date</th>
                                    <th>Training Module</th>
                                    <th class="text-center">Questions</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $result)
                                <tr>
                                    <td class="py-3">
                                        <span class="text-muted small">{{ $result->created_at->format('M d, Y') }}</span><br>
                                        <small>{{ $result->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 font-weight-bold">{{ $result->module->name ?? 'N/A' }}</h6>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-dark">{{ $result->correct_answers }} / {{ $result->total_questions_attempted }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="font-weight-bold {{ $result->is_passed ? 'text-success' : 'text-danger' }}">
                                            {{ round($result->percentage) }}%
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($result->is_passed)
                                            <label class="badge badge-success px-3 py-2 rounded-pill">
                                                <i class="mdi mdi-check"></i> Passed
                                            </label>
                                        @else
                                            <label class="badge badge-danger px-3 py-2 rounded-pill">
                                                <i class="mdi mdi-close"></i> Failed
                                            </label>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{-- If you have a detailed breakdown page --}}
                                        <a href="{{ route('exams.result', $result->id) }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" width="100" class="mb-3 opacity-5">
                                        <p class="text-muted">You haven't completed any assessments yet.</p>
                                        <a href="{{ route('exam.list') }}" class="btn btn-primary btn-sm">Start an Exam</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $results->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection