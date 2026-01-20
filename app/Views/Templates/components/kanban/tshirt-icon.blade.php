@props([
    'effort' => 3,
    'size' => 'md',
    'showLabel' => false
])

@php
use Leantime\Domain\Tickets\Models\TicketDesignTokens;

// Handle null/0/empty effort as "No Effort"
$isNoEffort = $effort === null || $effort === '' || $effort === 0 || $effort === '0';
$token = TicketDesignTokens::getEffort($effort);
$sizeLabel = $isNoEffort ? 'No Effort' : ($token['tshirtLabel'] ?? 'M');

$sizes = [
    'sm' => ['width' => 20, 'height' => 18],
    'md' => ['width' => 24, 'height' => 22],
    'lg' => ['width' => 30, 'height' => 28]
];

$sizeConfig = $sizes[$size] ?? $sizes['md'];
$color = '#159A80'; // Brand teal from design
@endphp

<span {{ $attributes->merge(['class' => 'tshirt-icon']) }}
      style="display: inline-flex; align-items: center; gap: 4px;"
      data-tippy-content="{{ $sizeLabel }} Effort">
    <svg
        width="{{ $sizeConfig['width'] }}"
        height="{{ $sizeConfig['height'] }}"
        viewBox="0 0 24 22"
        fill="none"
        style="flex-shrink: 0;">
        <!-- Plain t-shirt - NO text inside -->
        <path
            d="M8 1L4 1L1 5L4 7L4 21L20 21L20 7L23 5L20 1L16 1L14.5 3.5C14.5 3.5 13.5 5 12 5C10.5 5 9.5 3.5 9.5 3.5L8 1Z"
            fill="{{ $color }}"
            stroke="{{ $color }}"
            stroke-width="1.5"
            stroke-linejoin="round"/>
    </svg>

    @if($showLabel)
        <span style="font-size: 14px; font-weight: 500;">{{ $sizeLabel }}</span>
    @endif

    <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
        {{ $sizeLabel }} Effort
    </span>
</span>
