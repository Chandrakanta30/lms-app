@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Question Pool: {{ $document->doc_name }}</h3>
        <p class="text-muted">{{ $document->doc_number }} | v{{ $document->version }}</p>
    </div>

    <form action="{{ route('master-questions.sync', $document->id) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
            <table class="table table-bordered" id="questionTable">
    <thead>
        <tr>
            <th width="150px">Type</th>
            <th>Question / Statement</th>
            <th>Options / Correct Answer</th>
            <th width="50px"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($document->questions as $index => $q)
        <tr>
            <td>
                <select name="questions[{{ $index }}][question_type]" class="form-control type-select">
                    <option value="yes_no" {{ $q->question_type == 'yes_no' ? 'selected' : '' }}>Yes/No</option>
                    <option value="mcq" {{ $q->question_type == 'mcq' ? 'selected' : '' }}>MCQ</option>
                </select>
            </td>
            <td>
                <input type="text" name="questions[{{ $index }}][question_text]" class="form-control" value="{{ $q->question_text }}" required>
            </td>
            <td class="answer-cell">
                @if($q->question_type == 'mcq')
                    @foreach(range(0, 3) as $optionIndex)
                        <input
                            type="text"
                            name="questions[{{ $index }}][options][]"
                            class="form-control mb-1"
                            placeholder="Option {{ chr(65 + $optionIndex) }}"
                            value="{{ $q->options[$optionIndex] ?? '' }}">
                    @endforeach
                    <input type="text" name="questions[{{ $index }}][correct_answer]" class="form-control" placeholder="Correct Answer" value="{{ $q->correct_answer }}">
                @else
                    <select name="questions[{{ $index }}][correct_answer]" class="form-control">
                        <option value="Yes" {{ $q->correct_answer == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ $q->correct_answer == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
        </tr>
        @endforeach
    </tbody>
</table>

                <div class="mt-4">
                    <button type="button" class="btn btn-outline-primary" id="addRow">
                        <i class="mdi mdi-plus"></i> Add Question to Pool
                    </button>
                    
                    <div class="float-right">
                        <a href="{{ route('master-documents.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-success">Save Question Pool</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
   $(document).ready(function() {
    let rowIdx = {{ $document->questions->count() }};

    // 1. Add New Row
    $('#addRow').click(function() {
        let html = `
            <tr>
                <td>
                    <select name="questions[${rowIdx}][question_type]" class="form-control type-select">
                        <option value="yes_no">Yes/No</option>
                        <option value="mcq">MCQ</option>
                    </select>
                </td>
                <td><input type="text" name="questions[${rowIdx}][question_text]" class="form-control" required></td>
                <td class="answer-cell">
                    <select name="questions[${rowIdx}][correct_answer]" class="form-control">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
            </tr>`;
        $('#questionTable tbody').append(html);
        rowIdx++;
    });

    // 2. Toggle MCQ vs Yes/No Inputs
    $(document).on('change', '.type-select', function() {
        let cell = $(this).closest('tr').find('.answer-cell');
        let nameAttr = $(this).attr('name').replace('[question_type]', '');

        if ($(this).val() === 'mcq') {
            cell.html(`
                <input type="text" name="${nameAttr}[options][]" class="form-control mb-1" placeholder="Option A" required>
                <input type="text" name="${nameAttr}[options][]" class="form-control mb-1" placeholder="Option B" required>
                <input type="text" name="${nameAttr}[options][]" class="form-control mb-1" placeholder="Option C">
                <input type="text" name="${nameAttr}[options][]" class="form-control mb-1" placeholder="Option D">
                <input type="text" name="${nameAttr}[correct_answer]" class="form-control" placeholder="Correct Answer" required>
            `);
        } else {
            cell.html(`
                <select name="${nameAttr}[correct_answer]" class="form-control">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            `);
        }
    });

    // 3. Remove Row
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@endpush
