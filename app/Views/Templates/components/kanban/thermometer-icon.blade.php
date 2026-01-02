@props([
    'priority' => 3,
    'size' => 'md',
    'showLabel' => false
])

@php
use Leantime\Domain\Tickets\Models\TicketDesignTokens;

$token = TicketDesignTokens::getPriority($priority);
$fillPercent = $token['fill'] ?? 0.6;
$label = $token['label'] ?? 'Medium';
$color = $token['color'] ?? '#F5A623';

$sizes = [
    'sm' => ['width' => 16, 'height' => 24],
    'md' => ['width' => 18, 'height' => 28],
    'lg' => ['width' => 22, 'height' => 34]
];

$sizeConfig = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => 'thermometer-icon']) }}
      style="display: inline-flex; align-items: center; gap: 4px;"
      title="{{ $label }} Priority">
    <svg
        width="{{ $sizeConfig['width'] }}"
        height="{{ $sizeConfig['height'] }}"
        viewBox="0 0 14 24"
        style="flex-shrink: 0;">
        <!-- Background/outline -->
        <path
            d="M7 2C5 2 3.5 3.5 3.5 5.5V14.5C1.8 15.5 1 17 1 18.5C1 21 3 23 7 23C11 23 13 21 13 18.5C13 17 12.2 15.5 10.5 14.5V5.5C10.5 3.5 9 2 7 2Z"
            fill="#F5F5F0"
            stroke="#D4D4D4"
            stroke-width="1.5"/>

        <!-- Colored fill (varies by priority) -->
        <rect
            x="5"
            y="{{ 17 - ($fillPercent * 10) }}"
            width="4"
            height="{{ ($fillPercent * 10) + 1 }}"
            fill="{{ $color }}"/>

        <!-- Bulb (colored) -->
        <circle cx="7" cy="18.5" r="3.5" fill="{{ $color }}"/>

        <!-- Tick marks -->
        <line x1="10.5" y1="7" x2="12" y2="7" stroke="#D4D4D4" stroke-width="1"/>
        <line x1="10.5" y1="10" x2="12" y2="10" stroke="#D4D4D4" stroke-width="1"/>
        <line x1="10.5" y1="13" x2="12" y2="13" stroke="#D4D4D4" stroke-width="1"/>
    </svg>

    @if($showLabel)
        <span style="font-size: 14px; font-weight: 500; color: {{ $color }};">
            {{ $label }}
        </span>
    @endif

    <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
        {{ $label }} Priority
    </span>
</span>
