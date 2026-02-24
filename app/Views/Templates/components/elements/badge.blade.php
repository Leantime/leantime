@props([
    'asLink' => false,
    'color' => 'gray',
    'state' => null,
    'size' => null,
    'scale' => null,
    'icon' => null,
    'bgColor' => null,
    'dot' => false,
])

@php
    $resolvedColor = $state ?? $color;
    $resolvedSize = $scale ?? $size;

    $colorClass = match($resolvedColor) {
        'primary'   => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent'    => 'badge-primary',
        'info'      => 'badge-info',
        'success', 'green' => 'badge-success',
        'warning', 'yellow' => 'badge-warning',
        'error', 'danger', 'red' => 'badge-danger',
        'ghost'     => '',
        'outline'   => 'badge-outline',
        default     => '',
    };
    $sizeClass = match($resolvedSize) {
        'xs' => 'badge-xs',
        's', 'sm' => 'badge-sm',
        'l', 'lg' => 'badge-lg',
        default => '',
    };

    $badgeClasses = 'badge ' . $colorClass . ($sizeClass ? " $sizeClass" : '');
    $inlineStyle = $bgColor ? "background-color: {$bgColor}; border-color: {$bgColor};" : '';
@endphp

@if($dot)
    <span {{ $attributes->merge(['class' => 'tw:inline-flex tw:items-center tw:gap-1.5']) }}>
        <span class="tw:inline-block tw:w-2.5 tw:h-2.5 tw:rounded-full" style="{{ $inlineStyle ?: 'background-color: var(--accent1);' }}"></span>
        {{ $slot }}
    </span>
@elseif($asLink)
    <a {{ $attributes->merge([
        'class' => $badgeClasses,
        'href' => $url ?? '#',
    ] + ($inlineStyle ? ['style' => $inlineStyle] : [])) }}>
        @if($icon)<i class="{{ $icon }}"></i> @endif
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->merge([
        'class' => $badgeClasses,
    ] + ($inlineStyle ? ['style' => $inlineStyle] : [])) }}>
        @if($icon)<i class="{{ $icon }}"></i> @endif
        {{ $slot }}
    </span>
@endif
