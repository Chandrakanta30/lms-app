@extends('partials.app')
@section('content')
<div class="content-wrapper">
<div class="card shadow-sm">
    <div class="card-header bg-white font-weight-bold">
        Training Enrollment & Progress
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th>User</th>
                    <th>Training Module</th>
                    <th class="text-center">Progress</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trainees as $user)
                    @foreach($user->assigned_progress as $m)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->user_id }}</small>
                            </td>
                            <td>
                                <span class="font-weight-bold">{{ $m['name'] }}</span><br>
                                <span class="badge badge-{{ $m['color'] }} small">
                                    {{ ucfirst($m['status']) }}
                                </span>
                            </td>
                            <td style="width: 200px;">
                                <div class="progress mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $m['color'] }}" 
                                         style="width: {{ $m['percent'] }}%"></div>
                                </div>
                                <small class="d-block text-center text-muted">{{ $m['percent'] }}%</small>

                                <span class="badge badge-{{ $m['color'] }}">
                                    @if($m['id'] == 1)
                                <a href="{{ route('user.training.report', [$user->id, $m['id']]) }}" 
                                   class="btn btn-sm btn-outline-{{ $m['color'] }}">
                                    <i class="fas fa-file-alt"></i> View {{ $m['status'] }} Report
                                </a>
                                @endif
                                </span>
                            </td>
                            <td class="text-right">
                                {{-- Your Report Button --}}
                                <a href="{{ route('user.training.show', [$user->id, $m['id']]) }}" class="btn btn-primary btn-sm">
    Manage Training
</a>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>

@endsection