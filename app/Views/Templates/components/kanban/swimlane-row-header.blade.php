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

// Effort groupby shows size label
$effortLabel = ($groupBy === 'storypoints') ? (TicketDesignTokens::getEffort((float)$groupId)['tshirtLabel'] ?? '') : '';
@endphp

{{-- Compact vertical layout for 70px sidebar --}}
<div {{ $attributes->merge(['class' => 'accordion-toggle-swimlane']) }}
     data-swimlane-id="{{ $groupId }}"
     data-expanded="{{ $expanded ? 'true' : 'false' }}"
     tabindex="0"
     role="button"
     aria-expanded="{{ $expanded ? 'true' : 'false' }}"
     aria-controls="swimlane-content-{{ $groupId }}"
     aria-label="{{ strip_tags($label) }} - {{ $totalCount }} tasks - {{ $expanded ? 'Expanded' : 'Collapsed' }}"
     onclick="leantime.kanbanController.toggleSwimlane('{{ $groupId }}')"
     onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); leantime.kanbanController.toggleSwimlane('{{ $groupId }}'); }">

    {{-- Chevron indicator --}}
    <span class="kanban-lane-chevron" style="color: var(--primary-font-color); opacity: 0.6;">
        <i class="fa fa-chevron-{{ $expanded ? 'down' : 'right' }}"></i>
    </span>

    {{-- Visual indicator (icon/avatar) --}}
    @if($iconComponent)
        <div class="kanban-indicator">
            <x-dynamic-component :component="'global::kanban.' . $iconComponent" :attributes="new \Illuminate\View\ComponentAttributeBag($iconProps)" size="sm" />
        </div>
    @endif

    {{-- Effort size label (e.g., "M", "L") --}}
    @if($groupBy === 'storypoints' && $effortLabel)
        <span class="kanban-effort-indicator">{{ $effortLabel }}</span>
    @endif

    {{-- Count badge --}}
    <span class="kanban-lane-count" title="{{ $totalCount }} tasks">{{ $totalCount }}</span>

    {{-- Time alert indicator --}}
    @if($timeAlert)
        <x-global::kanban.time-indicator :type="$timeAlert" />
    @endif
</div>

{{-- Tooltip shown on hover (positioned by CSS) --}}
<div class="kanban-sidebar-tooltip">
    <div class="tooltip-label">{!! $label !!}</div>
    @if($moreInfo)
        <div class="tooltip-info">{!! $moreInfo !!}</div>
    @endif
    @if($totalCount > 0 && count($statusCounts) > 0)
        <div style="margin-top: 8px;">
            <x-global::kanban.micro-progress-bar
                :statusCounts="$statusCounts"
                :statusColumns="$statusColumns"
                :totalCount="$totalCount"
                :expandOnHover="false"
            />
        </div>
    @endif
</div>
