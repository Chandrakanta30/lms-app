@extends('partials.app')
@section('content')

<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h3 class="mb-4">Training Checklist: {{ $user->name }}</h3>
            
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $program->name }}</h5>
                        <span class="badge badge-pill badge-info">{{ $program->steps->count() }} Steps</span>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($program->steps as $step)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-primary font-weight-bold">Step {{ $step->step_number }}:</span> 
                                    {{ $step->name }}
                                </div>
                                
                                @if(in_array($step->id, $completedIds))
                                    <span class="badge badge-success px-3 py-2">
                                        <i class="mdi mdi-check-circle"></i> Completed
                                    </span>
                                @else
                                    {{-- Using both BS4 and BS5 data attributes for safety --}}
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-toggle="modal" data-target="#modalStep{{$step->id}}"
                                            data-bs-toggle="modal" data-bs-target="#modalStep{{$step->id}}">
                                        Log Step
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
        </div>
    </div>
</div>

{{-- MODALS LOOP: Keep this at the bottom, just before @endsection --}}

    @foreach($program->steps as $step)
        <div class="modal fade" id="modalStep{{$step->id}}" tabindex="-1" role="dialog" aria-labelledby="label{{$step->id}}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('user.training.store', [$user->id, $step->id]) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="label{{$step->id}}">Logging: {{ $step->name }}</h5>
                            {{-- Supports both BS4 and BS5 close buttons --}}
                            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="module_id" value="{{ $step->id }}">
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Interacted Person</label>
                                <input
                                    type="text"
                                    name="interacted_person"
                                    class="form-control"
                                    placeholder="Enter name"
                                    value="{{ old('interacted_person', $interactionDefaults['interacted_person'] ?? '') }}"
                                    required
                                >
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Designation</label>
                                <input
                                    type="text"
                                    name="designation"
                                    class="form-control"
                                    placeholder="e.g. Dept Head"
                                    value="{{ old('designation', $interactionDefaults['designation'] ?? '') }}"
                                    required
                                >
                            </div>
                            
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Comments</label>
                                <textarea name="comments" class="form-control" rows="3" placeholder="Additional notes...">{{ old('comments', $interactionDefaults['comments'] ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Training Log</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection
