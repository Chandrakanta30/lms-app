@extends('partials.app')

@section('content')
    <div class="content-wrapper">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="card-title mb-1">Passed Trainees</h4>
                            <p class="text-muted mb-0">Exam results for {{ $document->doc_name }}</p>
                        </div>
                        <a href="{{ route('master-documents.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back to Documents
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded align-items-end">
                    <div class="col-md-4">
                        <label for="user-filter" class="form-label">Passed User</label>
                        <select id="user-filter" name="user" class="form-select">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user['user_id'] }}" {{ request('user') == $user['user_id'] ? 'selected' : '' }}>
                                    {{ $user['user'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="training-type-filter" class="form-label">Training Type</label>
                        <select id="training-type-filter" name="training_type" class="form-select">
                            <option value="">All training types</option>
                            @foreach ($trainingTypes as $type)
                                <option value="{{ $type }}" {{ request('training_type') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('master-documents.results', $document->id) }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3">Passed User</th>
                                <th class="py-3">Training</th>
                                <th class="py-3">Training Type</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $result)
                                <tr>
                                    <td>{{ $result['user'] }}</td>
                                    <td>{{ $result['training'] }}</td>
                                    <td>{{ $result['training_type'] }}</td>
                                    <td>{{ $result['date'] ? \Carbon\Carbon::parse($result['date'])->format('d M Y') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted">No passed trainees found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
