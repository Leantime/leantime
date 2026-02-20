@props([
    'stageKey' => '',
    'stageNum' => 1,
    'color' => '#4A85B5',
    'bgColor' => '#EDF3F8',
    'icon' => 'fa-circle',
    'title' => '',
    'subtitle' => '',
    'active' => false,
    'itemCount' => 0,
    'focusLabel' => 'Current Focus',
])

<div class="sf-stage {{ $active ? 'active' : '' }}"
     data-s="{{ $stageNum }}"
     data-stage="{{ $stageKey }}"
     style="--stage-color: {{ $color }}; --stage-bg: {{ $bgColor }};">

    <div class="sf-flag" style="background: {{ $color }};">{{ $focusLabel }}</div>

    <div class="sf-hd">
        <div class="sf-icon"><i class="fa {{ $icon }}"></i></div>
        <div class="sf-title-row">
            <span class="sf-name">{{ $title }}</span>
            <span class="sf-count" style="background: {{ $color }};">{{ $itemCount }}</span>
        </div>
        <div class="sf-sub">{{ $subtitle }}</div>
        {{ $headerExtra ?? '' }}
    </div>

    {{ $beforeBody ?? '' }}

    <div class="sf-body">
        {{ $slot }}
    </div>
</div>
