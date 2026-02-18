@props([
    'asLink' => false,
    'color' => 'gray',
    'size' => null,
    'icon' => null,
    'bgColor' => null,
    'dot' => false,
])

@php
    $colorClass = match($color) {
        'primary'   => 'tw:badge-primary',
        'secondary' => 'tw:badge-secondary',
        'accent'    => 'tw:badge-accent',
        'info'      => 'tw:badge-info',
        'success', 'green' => 'tw:badge-success',
        'warning', 'yellow' => 'tw:badge-warning',
        'error', 'red' => 'tw:badge-error',
        'ghost'     => 'tw:badge-ghost',
        'outline'   => 'tw:badge-outline',
        default     => '',
    };
    $sizeClass = match($size) {
        'xs' => 'tw:badge-xs',
        'sm' => 'tw:badge-sm',
        'lg' => 'tw:badge-lg',
        default => '',
    };

    $badgeClasses = 'tw:badge ' . $colorClass . ($sizeClass ? " $sizeClass" : '');
    $inlineStyle = $bgColor ? "background-color: {$bgColor}; border-color: {$bgColor};" : '';
@endphp

@if($dot)
    <span {{ $attributes->merge(['class' => 'tw:inline-flex tw:items-center tw:gap-1.5']) }}>
        <span class="tw:inline-block tw:w-2.5 tw:h-2.5 tw:rounded-full {{ $colorClass }}" @if($inlineStyle) style="{{ $inlineStyle }}" @endif></span>
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
