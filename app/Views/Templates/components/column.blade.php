@props([
    'slug' => '',
    'index' => 1,
    'color' => '#4A85B5',
    'bgColor' => '#EDF3F8',
    'icon' => 'fa-circle',
    'title' => '',
    'subtitle' => '',
    'active' => false,
    'count' => 0,
    'focusLabel' => '',
])

{{--
    Column — a board lane: header (icon / title / count / subtitle) + a body of
    cards + an optional add affordance. Holds <x-global::card> (or anything) via
    the default slot; it does not depend on the card component.

    The active/inactive "focus" behaviour is opt-in via :active — when one column
    is active the others condense (used by the Logic Model board, which expands a
    stage at a time). A plain kanban/canvas column just passes :active="true" and
    no focusLabel.

    Slots: $headerExtra (inside the header, after the subtitle — must stay inside
    a position:relative header for absolutely-positioned plugin badges) and
    $beforeBody (between header and body). The board dispatches its extension
    events into these.

    `sf-*` classes are kept alongside `lt-*` for the StrategyPro plugin's
    scraping/styling during the migration window.
--}}
@include('global::components.column-styles')

<div {{ $attributes->merge(['class' => 'lt-column sf-stage' . ($active ? ' lt-column--active active' : '')]) }}
     data-index="{{ $index }}"
     data-s="{{ $index }}"
     data-key="{{ $slug }}"
     data-stage="{{ $slug }}"
     style="--stage-color: {{ $color }}; --stage-bg: {{ $bgColor }};">

    <div class="lt-column-flag" style="background: {{ $color }};">{{ $focusLabel }}</div>

    <div class="lt-column-header sf-hd">
        <div class="lt-column-icon"><i class="fa {{ $icon }}"></i></div>
        <div class="lt-column-title-row">
            <span class="lt-column-title sf-name">{{ $title }}</span>
            @if ($count > 0)<span class="lt-column-count" style="color: {{ $color }};">{{ $count }}</span>@endif
        </div>
        <div class="lt-column-sub sf-sub">{{ $subtitle }}</div>
        {{ $headerExtra ?? '' }}
    </div>

    {{ $beforeBody ?? '' }}

    <div class="lt-column-body sf-body">
        {{ $slot }}
    </div>
</div>
