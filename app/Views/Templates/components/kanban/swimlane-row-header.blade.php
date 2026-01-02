@props([
    'groupBy' => 'priority',
    'groupId' => null,
    'label' => '',
    'totalCount' => 0,
    'statusCounts' => [],
    'statusColumns' => [],
    'expanded' => true,
    'moreInfo' => null,
    'timeAlert' => null
])

@php
use Leantime\Domain\Tickets\Models\TicketDesignTokens;

// Determine which icon component to use
$iconComponent = match($groupBy) {
    'priority' => 'thermometer-icon',
    'storypoints' => 'tshirt-icon',
    'editorId' => 'user-avatar',
    'milestoneid' => 'milestone-icon',
    'type' => 'type-icon',
    'sprint' => 'sprint-icon',
    default => null
};

$iconProps = match($groupBy) {
    'priority' => ['priority' => (int)$groupId],
    'storypoints' => ['effort' => (float)$groupId],
    'editorId' => ['userId' => $groupId, 'username' => $label],
    'type' => ['type' => $groupId],
    default => ['label' => $label]
};

// Effort groupby shows size label next to icon
$showLabel = $groupBy !== 'effort';
$effortLabel = ($groupBy === 'effort') ? ($iconProps['effort'] ? TicketDesignTokens::getEffort($iconProps['effort'])['tshirtLabel'] ?? '' : '') : '';
@endphp

@php
// Count badge colors based on ranges
if ($totalCount >= 100) {
    $badgeTextColor = '#4A5A2F';
    $badgeBgColor = '#D4E0B8';
} elseif ($totalCount >= 10) {
    $badgeTextColor = '#5C6B3D';
    $badgeBgColor = '#E8EFD5';
} else {
    $badgeTextColor = '#6B7A4D';
    $badgeBgColor = '#F2F7E8';
}
@endphp

<!-- Add focus state styles -->
<style>
.swimlane-row-header:focus-visible {
    outline: 2px solid #4A90E2 !important;
    outline-offset: 2px !important;
    box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1) !important;
}

.swimlane-row-header:hover {
    background-color: #F5F5F0 !important;
}
</style>

<div {{ $attributes->merge(['class' => 'swimlane-row-header']) }}
     style="width: 150px; padding: 10px; background-color: #FAFAF5; border-radius: 8px; border: 1px solid #D4D4D4; display: flex; flex-direction: column; gap: {{ $expanded ? '8px' : '0' }}; box-shadow: 0 1px 2px rgba(0,0,0,0.04); cursor: pointer; transition: gap 0.2s ease;"
     data-swimlane-id="{{ $groupId }}"
     data-expanded="{{ $expanded ? 'true' : 'false' }}"
     tabindex="0"
     role="button"
     aria-expanded="{{ $expanded ? 'true' : 'false' }}"
     aria-controls="swimlane-content-{{ $groupId }}"
     aria-label="{{ strip_tags($label) }} - {{ $totalCount }} tasks - {{ $expanded ? 'Expanded' : 'Collapsed' }}"
     onclick="leantime.kanbanController.toggleSwimlane('{{ $groupId }}')"
     onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); leantime.kanbanController.toggleSwimlane('{{ $groupId }}'); }">

    @if($expanded)
        {{-- EXPANDED STATE: Full details with progress bar --}}

        <!-- Row 1: Chevron + Identifier + Label + Time Indicator -->
        <div style="display: flex; align-items: center; gap: 8px; padding: 0 0 8px 0;">
            <!-- Chevron -->
            <span style="font-size: 12px; color: #888888; transform: rotate(90deg); transition: transform 0.2s ease; flex-shrink: 0; width: 14px; display: inline-flex; align-items: center; justify-content: center;">▶</span>

            <!-- Visual Indicator -->
            @if($iconComponent)
                <x-dynamic-component :component="'global::kanban.' . $iconComponent" v-bind="$iconProps" size="md" />
            @endif

            <!-- Effort: Show size label (e.g., "M") -->
            @if($groupBy === 'effort' && $effortLabel)
                <span style="font-size: 14px; font-weight: 700; color: #333333;">{{ $effortLabel }}</span>
            @endif

            <!-- Other groupbys: Show full label -->
            @if($showLabel)
                @php
                // For milestones, show dates in title; otherwise just show label
                $titleText = $groupBy === 'milestoneid' && $moreInfo
                    ? strip_tags($label) . ' - ' . strip_tags($moreInfo)
                    : strip_tags($label);
                @endphp
                <span title="{{ $titleText }}"
                      style="font-size: 15px; font-weight: 500; color: #333333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; line-height: 1.4;">{!! $label !!}</span>
            @endif

            <!-- Time Indicator (inline) -->
            @if($timeAlert)
                <x-global::kanban.time-indicator :type="$timeAlert" />
            @endif
        </div>

        <!-- Row 2: Progress bar + Count Badge -->
        <div style="display: flex; align-items: center; gap: 10px; padding-left: 22px;">
            <!-- Progress Bar -->
            <div style="flex: 1;">
                @if($totalCount > 0)
                    <x-global::kanban.micro-progress-bar
                        :statusCounts="$statusCounts"
                        :statusColumns="$statusColumns"
                        :totalCount="$totalCount"
                        :expandOnHover="true"
                    />
                @endif
            </div>

            <!-- Count Badge (olive green background) -->
            <span title="{{ $totalCount }} total tasks"
                  aria-label="{{ $totalCount }} total tasks"
                  style="font-size: 13px; font-weight: 600; color: {{ $badgeTextColor }}; background-color: {{ $badgeBgColor }}; padding: 4px 9px; border-radius: 12px; min-width: {{ $totalCount >= 10 ? '32px' : '26px' }}; height: 24px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">{{ $totalCount }}</span>
        </div>

    @else
        {{-- COLLAPSED STATE: No progress bar, plain count number --}}

        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Chevron (pointing right) -->
            <span style="font-size: 12px; color: #888888; transform: rotate(0deg); transition: transform 0.2s ease; flex-shrink: 0; width: 14px; display: inline-flex; align-items: center; justify-content: center;">▶</span>

            <!-- Visual Indicator -->
            @if($iconComponent)
                <x-dynamic-component :component="'global::kanban.' . $iconComponent" v-bind="$iconProps" size="md" />
            @endif

            <!-- Effort: Show size label (e.g., "M") -->
            @if($groupBy === 'effort' && $effortLabel)
                <span style="font-size: 15px; font-weight: 500; color: #333333;">{{ $effortLabel }}</span>
            @endif

            <!-- Other groupbys: Show full label -->
            @if($showLabel)
                @php
                // For milestones, show dates in title; otherwise just show label
                $titleText = $groupBy === 'milestoneid' && $moreInfo
                    ? strip_tags($label) . ' - ' . strip_tags($moreInfo)
                    : strip_tags($label);
                @endphp
                <span title="{{ $titleText }}"
                      style="font-size: 15px; font-weight: 500; color: #333333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; line-height: 1.4;">{!! $label !!}</span>
            @endif

            <!-- Time Indicator (inline) -->
            @if($timeAlert)
                <x-global::kanban.time-indicator :type="$timeAlert" />
            @endif

            <!-- Plain Count (no background, just number) -->
            <span title="{{ $totalCount }} total tasks"
                  aria-label="{{ $totalCount }} total tasks"
                  style="font-size: 15px; font-weight: 500; color: #6B7280; flex-shrink: 0;">{{ $totalCount }}</span>
        </div>

    @endif
</div>
