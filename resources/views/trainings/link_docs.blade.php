@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Configure Exam: {{ $module->name }}</h3>
        <a href="{{ route('trainings.index') }}" class="btn btn-light btn-sm">Back to List</a>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Select Documents & Question Quota</h4>
                    <p class="card-description">Choose documents to include. The system will pull random questions from each document based on the quota you set.</p>

                    <form action="{{ route('admin.modules.saveLinks', $module->id) }}" method="POST" id="examConfigForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px;">Select</th>
                                        <th>Master Document Details</th>
                                        <th class="text-center">Total Pool Questions</th>
                                        <th style="width: 250px;">Questions Per Exam (Quota)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allDocs as $doc)
                                        @php 
                                            // Check if this document is already linked to the module
                                            $linkedDoc = $module->documents->where('id', $doc->id)->first();
                                            $isLinked = !is_null($linkedDoc);
                                            $currentQuota = $isLinked ? $linkedDoc->pivot->question_quota : 0;
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           name="docs[{{ $doc->id }}][selected]" 
                                                           class="form-check-input doc-checkbox" 
                                                           {{ $isLinked ? 'checked' : '' }}
                                                           {{ $doc->questions_count == 0 ? 'disabled' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">{{ $doc->doc_name }}</span><br>
                                                <small class="text-muted">{{ $doc->doc_number }} | {{ $doc->doc_type }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info shadow-sm">
                                                    {{ $doc->questions_count }} Available
                                                </span>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                <input type="number" 
                                                        name="docs[{{ $doc->id }}][quota]" 
                                                        class="form-control quota-input" 
                                                        value="{{ $currentQuota }}" 
                                                        min="1" 
                                                        max="{{ $doc->questions_count }}" 
                                                        placeholder="Qty"
                                                        {{ $isLinked ? 'required' : 'disabled' }} 
                                                        {{ $doc->questions_count == 0 ? 'disabled' : '' }}>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">of {{ $doc->questions_count }}</span>
                                                    </div>
                                                </div>
                                                @if($doc->questions_count == 0)
                                                    <small class="text-danger mt-1 d-block">Add questions to pool first!</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="mdi mdi-content-save"></i> Save Exam Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('examConfigForm');
    const checkboxes = document.querySelectorAll('.doc-checkbox');

    const toggleQuotaState = (checkbox) => {
        const row = checkbox.closest('tr');
        const quotaInput = row.querySelector('.quota-input');
        
        if (checkbox.checked) {
            // Enable and make required
            quotaInput.disabled = false;
            quotaInput.required = true;
            quotaInput.style.border = "1px solid #f44336"; 
            
            // Set default value if empty or 0
            if (!quotaInput.value || parseInt(quotaInput.value) <= 0) {
                quotaInput.value = 1;
            }
        } else {
            // Disable and remove required (removes validation block)
            quotaInput.disabled = true;
            quotaInput.required = false;
            quotaInput.style.border = "";
            quotaInput.classList.remove('is-invalid');
        }
    };

    // Run on change
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => toggleQuotaState(cb));
    });

    // Final Form Submit validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = "";

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const row = cb.closest('tr');
                const quotaInput = row.querySelector('.quota-input');
                const maxVal = parseInt(quotaInput.getAttribute('max'));
                const val = parseInt(quotaInput.value);

                if (!val || val <= 0) {
                    isValid = false;
                    quotaInput.classList.add('is-invalid');
                    errorMessage = "Please enter a valid quota for selected documents.";
                } else if (val > maxVal) {
                    isValid = false;
                    quotaInput.classList.add('is-invalid');
                    errorMessage = "Quota exceeds available questions.";
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
});
</script>
@endsection