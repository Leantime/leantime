@props([
    'size' => 'md',
])

@php
    $sizeClass = match($size) {
        'xs' => 'tw:loading-xs',
        'sm' => 'tw:loading-sm',
        'lg' => 'tw:loading-lg',
        default => 'tw:loading-md',
    };
@endphp

<span {{ $attributes->merge(['class' => "tw:loading tw:loading-spinner $sizeClass"]) }}></span>
