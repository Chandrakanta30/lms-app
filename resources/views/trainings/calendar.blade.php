@extends('partials.app')

@section('title', 'Training Calendar')

@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div>
                        <h4 class="card-title mb-1">Training Calendar</h4>
                        <p class="text-muted mb-0">View all training programs across the year and inspect monthly dates.</p>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <label for="calendarYear" class="mb-0 text-muted">Year</label>
                        <select id="calendarYear" class="form-control" style="width: 110px;">
                            @for ($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $y === now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div id="trainingCalendar"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('trainingCalendar');
            const yearSelect = document.getElementById('calendarYear');
            let currentYear = parseInt(yearSelect.value, 10);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'multiMonthYear',
                height: 'auto',
                navLinks: true,
                dayMaxEvents: true,
                eventDisplay: 'block',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth'
                },
                buttonText: {
                    today: 'Today',
                    multiMonthYear: 'Year',
                    dayGridMonth: 'Month'
                },
                views: {
                    multiMonthYear: {
                        titleFormat: {
                            year: 'numeric'
                        }
                    }
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch(`{{ route('training-calendar.events') }}?year=${currentYear}`)
                        .then(response => response.json())
                        .then(data => successCallback(data))
                        .catch(error => failureCallback(error));
                }
            });

            calendar.render();
            calendar.gotoDate(`${currentYear}-01-01`);

            yearSelect.addEventListener('change', function() {
                currentYear = parseInt(this.value, 10);
                calendar.gotoDate(`${currentYear}-01-01`);
                calendar.refetchEvents();
            });
        });
    </script>
@endpush
