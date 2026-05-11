@extends('partials.app')
@section('content')
    <div class="row  g-4"">
        <div class="col-md-6">
            <div class="card fixed-card">
                <div class="card-body">
                    <h4 class="card-title">Departments</h4>

                    <form action="{{ route('masters.dept.store') }}" method="POST" class="d-flex mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="Dept Name" required>

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </form>

                    <ul class="list-group scrollable-list">
                        @foreach ($departments as $dept)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $dept->name }}
                                <form action="{{ route('masters.dept.destroy', $dept->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs text-danger"> <i class="mdi mdi-trash-can"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card fixed-card">
                <div class="card-body">
                    <h4 class="card-title">Designations</h4>
                    <form action="{{ route('masters.desg.store') }}" method="POST" class="d-flex mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="e.g. Manager" required>
                        <button type="submit" class="btn btn-primary"> <i class="mdi mdi-plus"></i></button>
                    </form>
                    <ul class="list-group scrollable-list">
                        @foreach ($designations as $desg)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $desg->name }}
                                <form action="{{ route('masters.desg.destroy', $desg->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs text-danger"> <i class="mdi mdi-trash-can"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card fixed-card">
                <div class="card-body">
                    <h4 class="card-title">Venus</h4>
                    <form action="{{ route('masters.venue.store') }}" method="POST" class="form-inline mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="e.g. Room-1" required>
                        <button type="submit" class="btn btn-primary"> <i class="mdi mdi-plus"></i></button>
                    </form>
                    <ul class="list-group scrollable-list">
                        @foreach ($venues as $venue)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $venue->name }}

                                <form action="{{ route('masters.venue.destroy', $venue->venue_id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-xs text-danger"> <i class="mdi mdi-trash-can"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card fixed-card">
                <div class="card-body">
                    <h4 class="card-title">
                        Section
                    </h4>

                    <!-- Add Section -->
                    <form action="{{ route('masters.section.store') }}" method="POST" class="form-inline mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control mr-2" placeholder="e.g. Section-1"
                            required>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </form>

                    <!-- Section List -->
                    <ul class="list-group scrollable-list">
                        @foreach ($sections as $section)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $section->name }}

                                <form action="{{ route('masters.section.destroy', $section->sec_id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-xs text-danger"> <i class="mdi mdi-trash-can"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                </div>
            </div>
        </div>
    </div>
@endsection
