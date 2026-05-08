@extends('partials.app')

@section('content')
<style>
    /* Custom Radio Styling for "Card" feel */
    .answer-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        display: block;
        text-align: center;
        padding: 15px;
        background: #fff;
        margin-bottom: 0;
    }

    .answer-option:hover {
        background-color: #f8f9fa;
        border-color: #4B49AC;
    }

    /* Hide the actual radio input but keep it functional */
    .answer-radio {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* Change style when radio is checked */
    .answer-radio:checked + .answer-option {
        background-color: #4B49AC;
        border-color: #4B49AC;
        color: white;
        box-shadow: 0 4px 8px rgba(75, 73, 172, 0.3);
    }

    .question-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .sticky-header {
        position: sticky;
        top: 70px; /* Adjust based on your top navbar height */
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        border-bottom: 1px solid #eee;
    }
</style>

<div class="content-wrapper">
    <div class="sticky-header py-3 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="font-weight-bold mb-0">{{ $module->name }}</h4>
                    <div class="progress mt-2" style="height: 8px;">
                        <div id="examProgress" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
                    </div>
                </div>
                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <div class="d-inline-block px-3 py-2 bg-light rounded-pill">
                        <i class="mdi mdi-clock-outline text-danger"></i> 
                        <span id="timerText" class="font-weight-bold text-monospace h5">15:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('exams.submit', $module->id) }}" method="POST" id="examForm">
        @csrf
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                @foreach($examPaper as $index => $question)
                <div class="card question-card mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge badge-primary mr-3 p-2">Question {{ $index + 1 }}</span>
                            <h5 class="line-height-lg">{{ $question->question_text }}</h5>
                        </div>

                        <div class="row">
                            {{-- CASE 1: YES/NO QUESTIONS --}}
                            @if($question->question_type == 'yes_no' || $question->question_type == '')
                                <div class="col-6">
                                    <label class="w-100">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="Yes" class="answer-radio" data-qindex="{{ $index }}">
                                        <span class="answer-option">
                                            <i class="mdi mdi-check-circle-outline d-block mb-1" style="font-size: 20px;"></i>
                                            <strong>YES</strong>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="w-100">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="No" class="answer-radio" data-qindex="{{ $index }}">
                                        <span class="answer-option">
                                            <i class="mdi mdi-close-circle-outline d-block mb-1" style="font-size: 20px;"></i>
                                            <strong>NO</strong>
                                        </span>
                                    </label>
                                </div>

                            {{-- CASE 2: MCQ QUESTIONS --}}
                            @elseif($question->question_type == 'mcq')
                                @php 
                                    // Ensure options are handled as an array
                                    $options = is_array($question->options) ? $question->options : json_decode($question->options, true);
                                @endphp
                                
                                @if($options)
                                    @foreach($options as $optIndex => $option)
                                    <div class="col-md-6 mb-3">
                                        <label class="w-100">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ trim($option) }}" class="answer-radio" data-qindex="{{ $index }}">
                                            <span class="answer-option text-left">
                                                <span class="mr-2 font-weight-bold">{{ chr(65 + $optIndex) }}.</span> {{ trim($option) }}
                                            </span>
                                        </label>
                                    </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                    <div class="text-center py-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg" id="submitBtn">
                            Submit Assessment <i class="mdi mdi-arrow-right ml-2"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const totalQuestions = {{ $examPaper->count() }};
        const answeredQuestions = new Set();

        // Update progress bar as user answers
        $('.answer-radio').on('change', function() {
            const qIndex = $(this).data('qindex');
            answeredQuestions.add(qIndex);
            
            const progress = (answeredQuestions.size / totalQuestions) * 100;
            $('#examProgress').css('width', progress + '%');
        });

        // Countdown Timer Logic
        let timeInSeconds = 15 * 60; // 15 minutes
        const timerDisplay = $('#timerText');

        const interval = setInterval(function() {
            let mins = Math.floor(timeInSeconds / 60);
            let secs = timeInSeconds % 60;
            
            timerDisplay.text(`${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
            
            if (timeInSeconds <= 60) timerDisplay.parent().addClass('bg-warning');
            if (timeInSeconds <= 0) {
                clearInterval(interval);
                $('#examForm').submit();
            }
            timeInSeconds--;
        }, 1000);

        // Prevent page refresh/exit
        window.onbeforeunload = function() {
            return "Exam in progress! Are you sure you want to leave?";
        };

        $('#examForm').on('submit', function() {
            window.onbeforeunload = null;
        });
    });
</script>
@endpush
@endsection