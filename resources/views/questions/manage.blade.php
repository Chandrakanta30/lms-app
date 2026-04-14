@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Manage Exam: {{ $module->name }}</h3>
        <a href="{{ route('trainings.index') }}" class="btn btn-light">Back</a>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('questions.sync', $module->id) }}" method="POST">
                        @csrf
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
                    <input type="text" name="questions[{{ $index }}][options]" class="form-control mb-1" placeholder="Options (comma separated)" value="{{ implode(',', $q->options ?? []) }}">
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

                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">
                                <i class="mdi mdi-plus"></i> Add Question
                            </button>
                            <button type="submit" class="btn btn-success float-right">Save Question Paper</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
                <input type="text" name="${nameAttr}[options]" class="form-control mb-1" placeholder="Options (comma separated)" required>
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
