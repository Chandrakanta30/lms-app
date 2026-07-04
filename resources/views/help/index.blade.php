@extends('partials.app')
@section('title', 'Help / User Guide')

@section('content')

    <div class="page-intro">
        <span class="eyebrow">Vincatis LMS</span>
        <h2>Help &amp; User Guide</h2>
        <p>
            @if ($isAdmin)
                Everything you need to operate the system, organized by role. Browse any role's guide below, or jump straight to the FAQ.
            @else
                Everything you need to know for your role, plus answers to common questions. You only see what's relevant to you here.
            @endif
        </p>
    </div>

    <div class="card border mb-4 help-center" id="helpCenter">
        <div class="card-body">

            <div class="help-search mb-4">
                <i class="mdi mdi-magnify"></i>
                <input type="text" id="helpSearchInput" placeholder="Search topics&hellip; e.g. attendance, exam, documents, approve&hellip;" autocomplete="off">
            </div>

            <div class="help-tabbar mb-4" id="helpTabbar" role="tablist">
                @foreach ($sections as $key => $section)
                    <button type="button" class="help-tab {{ $key === $activeTab ? 'active' : '' }}" data-help-tab="{{ $key }}">
                        <i class="mdi mdi-{{ $section['icon'] }}"></i>
                        <span>{{ $section['label'] }}</span>
                    </button>
                @endforeach
            </div>

            @foreach ($sections as $key => $section)
                <div class="help-panel {{ $key === $activeTab ? '' : 'd-none' }}" data-help-panel="{{ $key }}">

                    <p class="text-muted mb-4">{!! $section['intro'] !!}</p>

                    <div class="help-grid">
                        @foreach ($section['cards'] as $card)
                            @php
                                $searchText = strip_tags(($card['title'] ?? '') . ' ' . ($card['summary'] ?? ''));
                            @endphp
                            <div class="help-card accent-{{ $card['accent'] }}" data-help-card data-search="{{ strtolower($searchText) }}">
                                <div class="help-card-head">
                                    <div class="help-card-icon">
                                        <i class="mdi mdi-{{ $card['icon'] }}"></i>
                                    </div>
                                    <div class="help-card-title-wrap">
                                        <h6 class="help-card-title">
                                            {!! $card['title'] !!}
                                            @if (!empty($card['badge']))
                                                <span class="badge badge-danger help-card-badge">{{ $card['badge'] }}</span>
                                            @endif
                                        </h6>
                                        <p class="help-card-summary">{!! $card['summary'] !!}</p>
                                    </div>
                                    <i class="mdi mdi-chevron-down help-card-chevron"></i>
                                </div>

                                @if (!empty($card['where']) || !empty($card['steps']) || !empty($card['notes']))
                                    <div class="help-card-detail">
                                        @if (!empty($card['where']))
                                            <p class="help-card-where"><strong>Where:</strong> {!! $card['where'] !!}</p>
                                        @endif

                                        @if (!empty($card['steps']))
                                            <ol class="help-card-steps">
                                                @foreach ($card['steps'] as $step)
                                                    <li>{!! $step !!}</li>
                                                @endforeach
                                            </ol>
                                        @endif

                                        @foreach ($card['notes'] as $note)
                                            <p class="help-card-note">{!! $note !!}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <p class="help-empty-state d-none">No topics match your search in this section.</p>
                </div>
            @endforeach

        </div>
    </div>

    <style>
        .help-search {
            position: relative;
        }
        .help-search .mdi-magnify {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a8aaae;
            font-size: 1.1rem;
        }
        .help-search input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border-radius: 10px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .help-search input:focus {
            border-color: #7367f0;
            box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.12);
        }

        .help-tabbar {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            background: rgba(15, 23, 42, 0.04);
            padding: 6px;
            border-radius: 12px;
        }
        .help-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            background: transparent;
            border-radius: 8px;
            color: #6b7280;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .help-tab:hover {
            color: #4b465c;
        }
        .help-tab.active {
            background: #fff;
            color: #7367f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }
        .help-tab i {
            font-size: 1rem;
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            align-items: start;
        }

        .help-card {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-left: 4px solid var(--accent-color, #7367f0);
            border-radius: 10px;
            padding: 16px;
            cursor: pointer;
            transition: box-shadow 0.2s, transform 0.15s;
        }
        .help-card:hover {
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .help-card.is-open {
            grid-column: 1 / -1;
        }

        .help-card-head {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .help-card-icon {
            flex: 0 0 auto;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-tint, rgba(115, 103, 240, 0.12));
            color: var(--accent-color, #7367f0);
            font-size: 1.15rem;
        }
        .help-card-title-wrap {
            flex: 1 1 auto;
            min-width: 0;
        }
        .help-card-title {
            margin: 0 0 4px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #2f2b3d;
        }
        .help-card-badge {
            margin-left: 6px;
            vertical-align: middle;
            font-size: 0.65rem;
        }
        .help-card-summary {
            margin: 0;
            font-size: 0.83rem;
            color: #6b7280;
            line-height: 1.45;
        }
        .help-card-chevron {
            flex: 0 0 auto;
            color: #a8aaae;
            transition: transform 0.2s;
            margin-top: 4px;
        }
        .help-card.is-open .help-card-chevron {
            transform: rotate(180deg);
        }

        .help-card-detail {
            display: none;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed rgba(15, 23, 42, 0.1);
        }
        .help-card.is-open .help-card-detail {
            display: block;
        }
        .help-card-where {
            font-size: 0.85rem;
            color: #4b465c;
            margin-bottom: 10px;
        }
        .help-card-steps {
            padding-left: 18px;
            margin-bottom: 10px;
        }
        .help-card-steps li {
            font-size: 0.87rem;
            color: #4b465c;
            margin-bottom: 6px;
        }
        .help-card-note {
            font-size: 0.83rem;
            color: #6b7280;
            background: rgba(15, 23, 42, 0.03);
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }

        .help-card.accent-blue   { --accent-color: #2563eb; --accent-tint: rgba(37,99,235,0.12); }
        .help-card.accent-green  { --accent-color: #16a34a; --accent-tint: rgba(22,163,74,0.12); }
        .help-card.accent-orange { --accent-color: #f97316; --accent-tint: rgba(249,115,22,0.12); }
        .help-card.accent-purple { --accent-color: #7c3aed; --accent-tint: rgba(124,58,237,0.12); }
        .help-card.accent-red    { --accent-color: #dc2626; --accent-tint: rgba(220,38,38,0.12); }
        .help-card.accent-teal   { --accent-color: #0d9488; --accent-tint: rgba(13,148,136,0.12); }
        .help-card.accent-indigo { --accent-color: #4f46e5; --accent-tint: rgba(79,70,229,0.12); }
        .help-card.accent-pink   { --accent-color: #db2777; --accent-tint: rgba(219,39,119,0.12); }

        .help-empty-state {
            text-align: center;
            color: #a8aaae;
            padding: 24px 0;
            font-size: 0.9rem;
        }
    </style>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var center = document.getElementById('helpCenter');
        if (!center) return;

        // Tab switching
        var tabs = center.querySelectorAll('[data-help-tab]');
        var panels = center.querySelectorAll('[data-help-panel]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var key = tab.getAttribute('data-help-tab');
                tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
                panels.forEach(function (p) {
                    p.classList.toggle('d-none', p.getAttribute('data-help-panel') !== key);
                });
                var input = document.getElementById('helpSearchInput');
                if (input) { input.value = ''; applySearch(''); }
            });
        });

        // Card expand/collapse
        center.querySelectorAll('[data-help-card]').forEach(function (card) {
            var head = card.querySelector('.help-card-head');
            head.addEventListener('click', function () {
                card.classList.toggle('is-open');
            });
        });

        // Search filter (scoped to the currently visible panel)
        function applySearch(query) {
            query = query.trim().toLowerCase();
            panels.forEach(function (panel) {
                if (panel.classList.contains('d-none')) return;
                var cards = panel.querySelectorAll('[data-help-card]');
                var visibleCount = 0;
                cards.forEach(function (card) {
                    var matches = !query || card.getAttribute('data-search').indexOf(query) !== -1;
                    card.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                });
                var empty = panel.querySelector('.help-empty-state');
                if (empty) empty.classList.toggle('d-none', visibleCount !== 0);
            });
        }

        var searchInput = document.getElementById('helpSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applySearch(searchInput.value);
            });
        }
    });
</script>
@endpush
