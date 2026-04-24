<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-3">Audit Logs</h3>

    <form method="GET" class="mb-3 d-flex gap-2">
        <select name="action" class="form-select" style="width:200px;">
            <option value="">All Actions</option>
            <option value="created">Created</option>
            <option value="updated">Updated</option>
            <option value="deleted">Deleted</option>
        </select>

        <button class="btn btn-primary">Filter</button>
    </form>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Who</th>
                <th>Action</th>
                <th>Model</th>
                <th>Changes</th>
                <th>Date</th>
            </tr>
        </thead>

       <tbody>
@foreach($logs as $log)
    <tr>
        <!-- WHO -->
        <td>{{ $log->causer->name ?? 'System' }}</td>

        <!-- ACTION BADGE -->
        <td>
            @if($log->description == 'created')
                <span class="badge bg-success">Created</span>
            @elseif($log->description == 'updated')
                <span class="badge bg-warning text-dark">Updated</span>
            @elseif($log->description == 'deleted')
                <span class="badge bg-danger">Deleted</span>
            @else
                <span class="badge bg-secondary">{{ $log->description }}</span>
            @endif
        </td>

        <!-- MODEL -->
        <td>{{ class_basename($log->subject_type) }}</td>

        <!-- CLEAN CHANGES -->
        <td>
            @if(isset($log->properties['attributes']))
                @foreach($log->properties['attributes'] as $key => $value)
                    <div>
                        <strong>{{ $key }}:</strong>
                        {{ $value }}

                        @if(isset($log->properties['old'][$key]))
                            <small class="text-muted">
                                (was: {{ $log->properties['old'][$key] }})
                            </small>
                        @endif
                    </div>
                @endforeach
            @endif
        </td>

        <!-- DATE -->
        <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
    </tr>
@endforeach
</tbody>


    </table>

    {{ $logs->links() }}
</div>

</body>
</html>