@extends('partials.app')

@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">

            <h4 class="mb-4">Training Audit Logs</h4>

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
                            <td>{{ $log->causer->name ?? 'System' }}</td>
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
                            <td>
                                @php
                                    $properties = $log->properties ?? [];
                                    $attributes = data_get($properties, 'attributes', []);
                                    $old = data_get($properties, 'old', []);
                                    $new = data_get($properties, 'new', []);
                                    $extras = collect($properties)->except(['attributes', 'old', 'new']);
                                @endphp

                                @if(count($attributes))
                                    @foreach($attributes as $key => $value)
                                        @php
                                            $oldValue = $old[$key] ?? null;
                                            $formattedNew = $value;
                                            $formattedOld = $oldValue;

                                            if ($key === 'is_active') {
                                                $formattedNew = $value ? 'Active' : 'Inactive';
                                                $formattedOld = isset($oldValue) ? ($oldValue ? 'Active' : 'Inactive') : null;
                                            }

                                            if ($key === 'training_type') {
                                                $formattedNew = ucfirst(str_replace('_', ' ', $value));
                                                $formattedOld = isset($oldValue) ? ucfirst(str_replace('_', ' ', $oldValue)) : null;
                                            }
                                        @endphp

                                        <div>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            @if(!is_null($formattedOld))
                                                <span class="text-danger">{{ $formattedOld }}</span> ->
                                            @endif
                                            <span class="text-success">{{ $formattedNew }}</span>
                                        </div>
                                    @endforeach
                                @elseif(count($new))
                                    @foreach($new as $key => $value)
                                        <div>
                                            <strong>{{ ucfirst($key) }}:</strong>
                                            <span class="text-danger">
                                                {{ implode(', ', (array) ($old[$key] ?? [])) }}
                                            </span>
                                            ->
                                            <span class="text-success">
                                                {{ implode(', ', (array) $value) }}
                                            </span>
                                        </div>
                                    @endforeach
                                @elseif($extras->isNotEmpty())
                                    @foreach($extras as $key => $value)
                                        <div>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ is_array($value) ? implode(', ', $value) : $value }}
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">No visible changes</span>
                                @endif
                            </td>
                            <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
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
