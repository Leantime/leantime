@props([
    'asLink' => false,
    'color' => 'gray',
    'size' => null,
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
@endphp

@if ($asLink)
    <a {{ $attributes->merge([
        'class' => 'tw:badge ' . $colorClass . ($sizeClass ? " $sizeClass" : ''),
        'href' => $url ?? '#',
    ]) }}>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->merge([
        'class' => 'tw:badge ' . $colorClass . ($sizeClass ? " $sizeClass" : ''),
    ]) }}>
        {{ $slot }}
    </span>
@endif
