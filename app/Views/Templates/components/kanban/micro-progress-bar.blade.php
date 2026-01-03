@props([
    'statusCounts' => [],
    'statusColumns' => [],
    'totalCount' => 0,
    'expandOnHover' => true,
    'size' => 'md'
])

@php
// Create segments for ALL status columns (even with 0 count)
// This ensures JavaScript can update any segment when tickets move
$segments = [];
foreach ($statusColumns as $statusId => $label) {
    $count = $statusCounts[$statusId] ?? 0;
    $percentage = ($totalCount > 0 && $count > 0) ? ($count / $totalCount) * 100 : 0;
    $segments[] = [
        'id' => $statusId,
        'count' => $count,
        'percentage' => round($percentage, 1),
        'label' => is_array($label) ? ($label['name'] ?? $label['label'] ?? "Status {$statusId}") : $label,
    ];
}

$heights = [
    'collapsed' => ['sm' => '4px', 'md' => '5px', 'lg' => '6px'],
    'expanded' => ['sm' => '18px', 'md' => '22px', 'lg' => '26px']
];
$collapsedHeight = $heights['collapsed'][$size] ?? '5px';
$expandedHeight = $heights['expanded'][$size] ?? '22px';
@endphp

<div {{ $attributes->merge(['class' => 'micro-progress-bar']) }}
     role="progressbar"
     aria-label="Status breakdown"
     style="position: relative; width: 100%;"
     onmouseenter="this.querySelector('.progress-segments').style.height='{{ $expandedHeight }}'; this.querySelector('.progress-segments').style.borderRadius='4px';"
     onmouseleave="this.querySelector('.progress-segments').style.height='{{ $collapsedHeight }}'; this.querySelector('.progress-segments').style.borderRadius='2.5px';">

    <div class="progress-segments"
         style="display: flex; align-items: stretch; height: {{ $collapsedHeight }}; border-radius: 2.5px; overflow: hidden; background-color: #D4D4D4; width: 100%; transition: height 0.2s ease, border-radius 0.2s ease; cursor: {{ $expandOnHover && $totalCount > 0 ? 'pointer' : 'default' }};">
        @foreach($segments as $segment)
            @if($segment['percentage'] > 0)
            <div class="status-segment status-{{ $segment['id'] }}"
                 style="flex: {{ $segment['percentage'] }} 1 0%; overflow: hidden;"
                 data-tippy-content="{{ $segment['label'] }}: {{ $segment['count'] }}">
                <span class="segment-count">{{ $segment['count'] }}</span>
            </div>
            @endif
        @endforeach
    </div>

    @if($totalCount > 0)
        <!-- Screen reader summary -->
        <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
            @foreach($segments as $segment)
                @if($segment['count'] > 0)
                    {{ $segment['label'] }}: {{ $segment['count'] }}.
                @endif
            @endforeach
        </span>
    @else
        <!-- Empty state for screen readers -->
        <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
            No tasks in this group.
        </span>
    @endif
</div>
