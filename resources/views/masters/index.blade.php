@extends('partials.app')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Departments</h4>
                    <form action="{{ route('masters.dept.store') }}" method="POST" class="form-inline mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="Dept Name" required>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                    <ul class="list-group">
                        @foreach ($departments as $dept)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $dept->name }}
                                <form action="{{ route('masters.dept.destroy', $dept->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs text-danger">Delete</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Designations</h4>
                    <form action="{{ route('masters.desg.store') }}" method="POST" class="form-inline mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="e.g. Manager" required>
                        <button type="submit" class="btn btn-info">Add</button>
                    </form>
                    <ul class="list-group">
                        @foreach ($designations as $desg)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $desg->name }}
                                <form action="{{ route('masters.desg.destroy', $desg->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs text-danger">Delete</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Venus</h4>
                    <form action="{{ route('masters.venue.store') }}" method="POST" class="form-inline mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="e.g. Room-1" required>
                        <button type="submit" class="btn btn-info">Add</button>
                    </form>
                    <ul class="list-group">
                        @foreach ($venues as $venue)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $venue->name }}

                                <form action="{{ route('masters.venue.destroy', $venue->venue_id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-xs text-danger">Delete</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
