@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
                        <div>
                            <h3 class="mb-1">{{ $module->name }} Reading Room</h3>
                            <p class="text-muted mb-0">Review the tagged documents fully before the assessment is unlocked.</p>
                        </div>
                        <div class="mt-3 mt-lg-0 text-lg-right">
                            <div class="small text-muted">Required reading time</div>
                            <div class="h4 mb-0 text-primary" id="readingTimer">
                                {{ gmdate('i:s', $remainingSeconds) }}
                            </div>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        The assessment button stays disabled until the countdown reaches zero and you submit reading completion.
                    </div>

                    <div class="row">
                        @foreach($module->documents as $document)
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">{{ $document->doc_name }}</h5>
                                            <div class="small text-muted">{{ $document->doc_number }} • {{ $document->doc_type }}</div>
                                        </div>
                                        <span class="badge badge-dark">{{ $document->pivot->question_quota ?? 0 }} Q</span>
                                    </div>
                                    <p class="small text-muted mb-3">Open and read this document before proceeding to the assessment.</p>
                                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="mdi mdi-open-in-new"></i> Open Document
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form action="{{ route('exams.read.complete', $module->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <a href="{{ route('exam.list') }}" class="btn btn-light mb-3 mb-md-0">Back to Assessments</a>
                            <button type="submit" class="btn btn-success px-4" id="completeReadingBtn" {{ $remainingSeconds > 0 ? 'disabled' : '' }}>
                                <i class="mdi mdi-check-circle-outline"></i> Complete Reading and Unlock Assessment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var remainingSeconds = {{ (int) $remainingSeconds }};
        var timerEl = document.getElementById('readingTimer');
        var completeButton = document.getElementById('completeReadingBtn');

        function formatTime(totalSeconds) {
            var minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
            var seconds = (totalSeconds % 60).toString().padStart(2, '0');
            return minutes + ':' + seconds;
        }

        function tick() {
            timerEl.textContent = formatTime(remainingSeconds);

            if (remainingSeconds <= 0) {
                completeButton.removeAttribute('disabled');
                return;
            }

            remainingSeconds -= 1;
            setTimeout(tick, 1000);
        }

        tick();
    })();
</script>
@endpush
@endsection
