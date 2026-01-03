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
    'effort' => 'tshirt-icon',
    'editorId' => 'user-avatar',
    'milestoneid' => 'milestone-icon',
    'type' => 'type-icon',
    'sprint' => 'sprint-icon',
    default => null // Status and other groupings use FontAwesome icon below
};

// For groupBy types without a component, use FontAwesome icon
$faIcon = match($groupBy) {
    'status' => 'fa-circle-dot',
    default => 'fa-layer-group'
};

$iconProps = match($groupBy) {
    'priority' => ['priority' => (int)$groupId],
    'storypoints' => ['effort' => (float)$groupId],
    'effort' => ['effort' => (float)$groupId],
    'editorId' => ['userId' => $groupId, 'username' => $label],
    'type' => ['type' => $groupId],
    default => ['label' => $label]
};

// Effort groupby shows size label next to icon
$effortLabel = '';
if (in_array($groupBy, ['storypoints', 'effort'])) {
    $effortLabel = TicketDesignTokens::getEffort((float)$groupId)['tshirtLabel'] ?? '';
}

// Transform statusColumns for micro-progress-bar
$statusLabels = [];
foreach ($statusColumns as $statusId => $statusData) {
    if (is_array($statusData)) {
        $statusLabels[$statusId] = $statusData['name'] ?? $statusData['label'] ?? "Status $statusId";
    } else {
        $statusLabels[$statusId] = $statusData;
    }
}
@endphp

{{-- PRD v2 Compliant: 150px horizontal layout with two rows --}}
{{-- Header looks IDENTICAL in expanded and collapsed states --}}
<div {{ $attributes->merge(['class' => 'kanban-swimlane-sidebar']) }}
     data-swimlane-id="{{ $groupId }}"
     tabindex="0"
     role="button"
     aria-expanded="{{ $expanded ? 'true' : 'false' }}"
     aria-controls="swimlane-content-{{ $groupId }}"
     aria-label="{{ strip_tags($label) }} - {{ $totalCount }} tasks - {{ $expanded ? 'Expanded' : 'Collapsed' }}"
     onclick="leantime.kanbanController.toggleSwimlane('{{ $groupId }}')"
     onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); leantime.kanbanController.toggleSwimlane('{{ $groupId }}'); }">

    {{-- Row 1: Chevron + Icon + Label + Time Indicator --}}
    <div class="swimlane-header-row1">
        {{-- Chevron (▼ expanded, ▶ collapsed) --}}
        <span class="kanban-lane-chevron">
            <i class="fa fa-chevron-{{ $expanded ? 'down' : 'right' }}"></i>
        </span>

        {{-- Visual indicator (icon/avatar) --}}
        @if($iconComponent)
            <div class="kanban-indicator">
                <x-dynamic-component
                    :component="'global::kanban.' . $iconComponent"
                    :attributes="new \Illuminate\View\ComponentAttributeBag($iconProps)"
                    size="md"
                />
            </div>
        @else
            {{-- Default FontAwesome icon for status and other groupings --}}
            <span class="kanban-indicator">
                <i class="fa {{ $faIcon }} kanban-indicator-icon"></i>
            </span>
        @endif

        {{-- Label - truncates with ellipsis --}}
        <span class="swimlane-header-label" title="{{ strip_tags($label) }}">
            {!! $label !!}
        </span>

        {{-- Time indicator (⏳ ⏰ 💤) --}}
        @if($timeAlert)
            <span class="swimlane-time-indicator">
                <x-global::kanban.time-indicator :type="$timeAlert" />
            </span>
        @endif
    </div>

    {{-- Row 2: Progress Bar + Count Badge --}}
    <div class="swimlane-header-row2">
        {{-- Micro Progress Bar (status breakdown) --}}
        @if($totalCount > 0 && count($statusCounts) > 0)
            <div style="flex: 1; min-width: 0;">
                <x-global::kanban.micro-progress-bar
                    :statusCounts="$statusCounts"
                    :statusColumns="$statusLabels"
                    :totalCount="$totalCount"
                    :expandOnHover="true"
                    size="lg"
                />
            </div>
        @else
            <div style="flex: 1;"></div>
        @endif

        {{-- Count Badge --}}
        <span class="kanban-lane-count" title="{{ $totalCount }} tasks">{{ $totalCount }}</span>
    </div>
</div>

{{-- Tooltip shown on hover for long labels --}}
@if(strlen(strip_tags($label)) > 12 || $moreInfo)
<div class="kanban-sidebar-tooltip">
    <div class="tooltip-label">{!! $label !!}</div>
    @if($moreInfo)
        <div class="tooltip-info">{!! $moreInfo !!}</div>
    @endif
</div>
@endif
