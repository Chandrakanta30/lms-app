@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center">
                    @if($result->is_passed)
                        <i class="mdi mdi-checkbox-marked-circle-outline text-success" style="font-size: 80px;"></i>
                        <h2 class="text-success mt-3">PASSED!</h2>
                    @else
                        <i class="mdi mdi-alert-circle-outline text-danger" style="font-size: 80px;"></i>
                        <h2 class="text-danger mt-3">FAILED</h2>
                    @endif

                    <h4 class="mt-4">Module: {{ $result->module->name }}</h4>
                    <div class="display-4 my-3">{{ round($result->percentage) }}%</div>
                    
                    <p class="lead">
                        You got <strong>{{ $result->correct_answers }}</strong> out of 
                        <strong>{{ $result->total_questions_attempted }}</strong> questions correct.
                    </p>

                    <hr>

                    <div class="mt-4">
                        @if($result->is_passed)
                            <a href="{{ route('exams.details', $result->id) }}" class="btn btn-outline-primary px-4 mr-2">View Details</a>
                            <a href="{{ route('trainings.index') }}" class="btn btn-primary px-5">Back to Dashboard</a>
                        @else
                            <!-- <a href="{{ route('exams.take', $result->training_module_id) }}" class="btn btn-warning px-5">Retry Exam</a> -->
                            <a href="{{ route('exams.details', $result->id) }}" class="btn btn-outline-primary px-4">View Details</a>
                            <p class="mt-3 text-muted small">Tip: Re-read the SOP before retrying.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
