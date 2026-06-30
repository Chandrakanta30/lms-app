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
                                <p class="text-muted mb-0">Review the tagged documents fully before the assessment is
                                    unlocked.</p>
                            </div>
                            <div class="mt-3 mt-lg-0 text-lg-right">
                                <div class="small text-muted">Required reading time</div>
                                <div class="h4 mb-0 text-primary" id="readingTimer"
                                    data-remaining-seconds="{{ (int) $remainingSeconds }}">
                                    {{ $remainingSeconds > 3600 ? gmdate('H:i:s', $remainingSeconds) : gmdate('i:s', $remainingSeconds) }}
                                </div>
                            </div>
                        </div>

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="alert alert-info">
                            The assessment button stays disabled until the countdown reaches zero and you submit reading
                            completion.
                        </div>

                        <div class="row">
                            @foreach ($module->documents as $document)
                                <div class="col-md-6 mb-4">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h5 class="mb-1">{{ $document->doc_name }}</h5>
                                                <div class="small text-muted">{{ $document->doc_number }} •
                                                    {{ $document->doc_type }}</div>
                                            </div>
                                            <span class="badge badge-dark">{{ $document->pivot->question_quota ?? 0 }}
                                                Q</span>
                                        </div>
                                        <p class="small text-muted mb-3">Open and read this document before proceeding to
                                            the assessment.</p>
                                        @php
                                            $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                                        @endphp

                                        <a href="javascript:void(0)"
                                            onclick="openDocument(
       '{{ route('documents.view', ['id' => $document->id, 'secure' => 1]) }}',
       '{{ $extension }}'
   )"
                                            class="btn btn-outline-primary btn-sm">

                                            <i class="mdi mdi-open-in-new"></i> Open Document
                                        </a>
                                        <div class="small text-muted mt-2">
                                            Read time: {{ $document->read_time_label }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form action="{{ route('exams.read.complete', $module->id) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                <a href="{{ route('exam.list') }}" class="btn btn-light mb-3 mb-md-0">Back to
                                    Assessments</a>
                                <button type="submit" class="btn btn-success px-4" id="completeReadingBtn"
                                    {{ $remainingSeconds > 0 ? 'disabled' : '' }}>
                                    <i class="mdi mdi-check-circle-outline"></i> Complete Reading and Unlock Assessment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Document Preview</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body p-0 text-center">

                    <!-- PDF / DOC iframe -->
                    <iframe id="documentFrame" width="100%" height="700px" style="border:none; display:none;">
                    </iframe>

                    <!-- Image Preview -->
                    <img id="imagePreview" src="" class="img-fluid" style="display:none; max-height:700px;">

                    <!-- DOCX Preview -->
                    <div id="docxPreview" class="text-left p-4 bg-white"
                        style="display:none; max-height:700px; overflow:auto;">
                    </div>

                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/mammoth@1.8.0/mammoth.browser.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            (function() {
                var remainingSeconds = {{ (int) $remainingSeconds }};
                var timerEl = document.getElementById('readingTimer');
                var completeButton = document.getElementById('completeReadingBtn');
                var endAt = Date.now() + (remainingSeconds * 1000);

                function formatTime(totalSeconds) {
                    var hours = Math.floor(totalSeconds / 3600);
                    var minutes = Math.floor((totalSeconds % 3600) / 60);
                    var seconds = totalSeconds % 60;

                    if (hours > 0) {
                        return [
                            hours.toString().padStart(2, '0'),
                            minutes.toString().padStart(2, '0'),
                            seconds.toString().padStart(2, '0')
                        ].join(':');
                    }

                    return [
                        minutes.toString().padStart(2, '0'),
                        seconds.toString().padStart(2, '0')
                    ].join(':');
                }

                function tick() {
                    remainingSeconds = Math.max(0, Math.ceil((endAt - Date.now()) / 1000));
                    timerEl.textContent = formatTime(remainingSeconds);

                    if (remainingSeconds <= 0) {
                        completeButton.removeAttribute('disabled');
                        return;
                    }

                    setTimeout(tick, 1000);
                }

                tick();
            })();
            const securePreviewSelectors = ['#documentFrame', '#imagePreview', '#docxPreview'];

            function blockSecurePreviewActions(event) {
                event.preventDefault();
                return false;
            }

            function handleSecurePreviewKeys(event) {
                const key = (event.key || '').toLowerCase();
                const ctrlOrMeta = event.ctrlKey || event.metaKey;

                if (
                    (ctrlOrMeta && ['c', 's', 'p', 'u'].includes(key)) ||
                    key === 'printscreen' ||
                    key === 'f12'
                ) {
                    event.preventDefault();
                    return false;
                }
            }

            function enableSecurePreviewLock() {
                securePreviewSelectors.forEach((selector) => {
                    const element = document.querySelector(selector);

                    if (!element) {
                        return;
                    }

                    element.setAttribute('oncontextmenu', 'return false');
                    element.style.userSelect = 'none';
                    element.style.webkitUserSelect = 'none';
                });

                document.addEventListener('contextmenu', blockSecurePreviewActions);
                document.addEventListener('copy', blockSecurePreviewActions);
                document.addEventListener('cut', blockSecurePreviewActions);
                document.addEventListener('dragstart', blockSecurePreviewActions);
                document.addEventListener('keydown', handleSecurePreviewKeys);
            }

            function disableSecurePreviewLock() {
                document.removeEventListener('contextmenu', blockSecurePreviewActions);
                document.removeEventListener('copy', blockSecurePreviewActions);
                document.removeEventListener('cut', blockSecurePreviewActions);
                document.removeEventListener('dragstart', blockSecurePreviewActions);
                document.removeEventListener('keydown', handleSecurePreviewKeys);
            }

            async function renderPdfAsCanvas(url, container) {
                container.innerHTML = '<div class="p-3 text-muted">Loading PDF preview...</div>';

                const pdfjs = window.pdfjsLib;
                if (!pdfjs) {
                    container.innerHTML = '<div class="p-3 text-danger">PDF preview library failed to load.</div>';
                    return;
                }

                pdfjs.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                const loadingTask = pdfjs.getDocument(url);
                const pdf = await loadingTask.promise;

                container.innerHTML = '';

                for (let pageNo = 1; pageNo <= pdf.numPages; pageNo++) {
                    const page = await pdf.getPage(pageNo);
                    const viewport = page.getViewport({
                        scale: 1.25
                    });

                    const canvas = document.createElement('canvas');
                    canvas.className = 'mb-3';
                    canvas.style.maxWidth = '100%';
                    canvas.style.height = 'auto';
                    canvas.style.display = 'block';
                    canvas.style.margin = '0 auto';

                    const context = canvas.getContext('2d', {
                        alpha: false
                    });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;

                    container.appendChild(canvas);
                }
            }

            async function openDocument(url, extension) {
                let frame = document.getElementById('documentFrame');
                let image = document.getElementById('imagePreview');
                let docxPreview = document.getElementById('docxPreview');

                frame.style.display = 'none';
                image.style.display = 'none';
                docxPreview.style.display = 'none';
                docxPreview.innerHTML = '';

                // IMAGE FILES
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {

                    image.src = url;
                    image.style.display = 'block';
                }

                // DOCX rendered as HTML
                else if (extension === 'docx') {

                    try {
                        let response = await fetch(url);
                        let arrayBuffer = await response.arrayBuffer();
                        let result = await mammoth.convertToHtml({
                            arrayBuffer: arrayBuffer
                        });

                        docxPreview.innerHTML = result.value ||
                            '<p class="text-muted mb-0">No preview content available.</p>';
                        docxPreview.style.display = 'block';
                    } catch (error) {
                        docxPreview.innerHTML =
                            '<div class="p-4 text-center text-danger">Unable to preview this DOCX file inline.</div>';
                        docxPreview.style.display = 'block';
                    }
                }

                // PDF / browser-previewable document files
                else if (extension === 'pdf') {
                    try {
                        docxPreview.style.display = 'block';
                        await renderPdfAsCanvas(url, docxPreview);
                    } catch (error) {
                        docxPreview.innerHTML =
                            '<div class="p-4 text-center text-danger">Unable to preview this PDF inline.</div>';
                        docxPreview.style.display = 'block';
                    }
                } else if (['ppt', 'pptx'].includes(extension)) {
                    frame.src = url;
                    frame.style.display = 'block';
                }

                // OTHER FILES
                else {

                    window.open(url, '_blank');
                    return;
                }

                let modal = new bootstrap.Modal(
                    document.getElementById('documentModal')
                );

                enableSecurePreviewLock();
                modal.show();
            }

            // Clear modal on close
            document.getElementById('documentModal')
                .addEventListener('hidden.bs.modal', function() {

                    disableSecurePreviewLock();
                    document.getElementById('documentFrame').src = '';
                    document.getElementById('imagePreview').src = '';
                    document.getElementById('docxPreview').innerHTML = '';
                });
            document.addEventListener('keyup', function(e) {
                if (e.key === 'PrintScreen') {

                    document.body.style.filter = 'blur(8px)';

                    setTimeout(() => {
                        document.body.style.filter = 'none';
                    }, 2000);
                }
            });
    
        </script>
    @endpush
@endsection
