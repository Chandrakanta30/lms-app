@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">

            <h4 class="mb-4">Training Audit Logs</h4>

            {{-- Optional: Show Training Name --}}
            @if($logs->count())
                <div class="mb-3">
                    <strong>Training:</strong>
                    <span class="text-primary">
                        {{ optional($logs->first()->subject)->name ?? 'N/A' }}
                    </span>
                </div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Changes</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            {{-- USER --}}
                            <td>{{ $log->causer->name ?? 'System' }}</td>

                            {{-- ACTION --}}
                            <td>
                                <span class="badge 
                                    @if($log->description == 'created') badge-success
                                    @elseif($log->description == 'updated') badge-warning
                                    @elseif($log->description == 'deleted') badge-danger
                                    @else badge-secondary
                                    @endif
                                ">
                                    {{ ucfirst($log->description) }}
                                </span>
                            </td>

                            {{-- CHANGES --}}
                            <td>
    @php
        $attributes = $log->properties['attributes'] ?? [];
        $old = $log->properties['old'] ?? [];
        $new = $log->properties['new'] ?? [];
    @endphp

    {{-- CASE 1: Default Laravel updates (attributes) --}}
    @if(count($attributes))
        @foreach($attributes as $key => $value)
            @php
                $oldValue = $old[$key] ?? null;

                $formattedNew = $value;
                $formattedOld = $oldValue;

                if ($key == 'is_active') {
                    $formattedNew = $value ? 'Active' : 'Inactive';
                    $formattedOld = isset($oldValue) ? ($oldValue ? 'Active' : 'Inactive') : null;
                }

                if ($key == 'training_type') {
                    $formattedNew = ucfirst(str_replace('_', ' ', $value));
                    $formattedOld = isset($oldValue) ? ucfirst(str_replace('_', ' ', $oldValue)) : null;
                }
            @endphp

            <div>
                <strong>{{ ucfirst(str_replace('_',' ', $key)) }}:</strong>

                @if(!is_null($formattedOld))
                    <span class="text-danger">{{ $formattedOld }}</span> →
                @endif

                <span class="text-success">{{ $formattedNew }}</span>
            </div>
        @endforeach

    {{-- CASE 2: Custom logs (trainers / trainees) --}}
    @elseif(count($new))
        @foreach($new as $key => $value)
            <div>
                <strong>{{ ucfirst($key) }}:</strong>

                <span class="text-danger">
                    {{ implode(', ', $old[$key] ?? []) }}
                </span>
                →
                <span class="text-success">
                    {{ implode(', ', $value ?? []) }}
                </span>
            </div>
        @endforeach

    {{-- CASE 3: Nothing --}}
    @else
        <span class="text-muted">No visible changes</span>
    @endif
</td>

                            {{-- DATE --}}
                            <td>
                                {{ $log->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No audit logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection