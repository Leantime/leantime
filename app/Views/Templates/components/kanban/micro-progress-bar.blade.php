@props([
    'statusCounts' => [],
    'statusColumns' => [],
    'totalCount' => 0,
    'expandOnHover' => true,
    'size' => 'md'
])

@php
// Calculate percentages
$segments = [];
if ($totalCount > 0) {
    foreach ($statusCounts as $statusId => $count) {
        if ($count > 0) {
            $percentage = ($count / $totalCount) * 100;
            $label = $statusColumns[$statusId] ?? "Status {$statusId}";
            $segments[] = [
                'id' => $statusId,
                'count' => $count,
                'percentage' => round($percentage, 1),
                'label' => $label,
            ];
        }
    }
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
         style="display: flex; height: {{ $collapsedHeight }}; border-radius: 2.5px; overflow: hidden; background-color: #D4D4D4; width: 100%; transition: height 0.2s ease, border-radius 0.2s ease; cursor: {{ $expandOnHover && count($segments) > 0 ? 'pointer' : 'default' }};">
        @if(count($segments) > 0)
            @foreach($segments as $segment)
                <div class="status-segment status-{{ $segment['id'] }}"
                     style="width: {{ $segment['percentage'] }}%; display: flex; align-items: center; justify-content: center;"
                     title="{{ $segment['label'] }}: {{ $segment['count'] }}">
                    @if($segment['percentage'] > 12)
                        <span class="segment-count"
                              style="font-size: 12px; font-weight: 700; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3); opacity: 0; transition: opacity 0.2s ease;">{{ $segment['count'] }}</span>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    @if(count($segments) > 0)
        <!-- Show counts on hover -->
        <style>
            .micro-progress-bar:hover .segment-count {
                opacity: 1 !important;
            }
        </style>

        <!-- Screen reader summary -->
        <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
            @foreach($segments as $segment)
                {{ $segment['label'] }}: {{ $segment['count'] }}.
            @endforeach
        </span>
    @else
        <!-- Empty state for screen readers -->
        <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
            No tasks in this group.
        </span>
    @endif
</div>
