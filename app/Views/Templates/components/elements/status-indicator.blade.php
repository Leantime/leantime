@props([
    'status' => 'default',
    'label' => null,
    'size' => 'sm',
])

@php
    $colorClass = match($status) {
        'success', 'green'  => 'tw:bg-success',
        'warning', 'yellow' => 'tw:bg-warning',
        'error', 'red'      => 'tw:bg-error',
        'info', 'blue'      => 'tw:bg-info',
        default              => 'tw:bg-neutral',
    };
    $sizeStyle = match($size) {
        'xs' => 'width: 6px; height: 6px;',
        'sm' => 'width: 8px; height: 8px;',
        'md' => 'width: 10px; height: 10px;',
        'lg' => 'width: 14px; height: 14px;',
        default => 'width: 8px; height: 8px;',
    };
@endphp

<span {{ $attributes->merge(['class' => 'tw:inline-flex tw:items-center tw:gap-1.5']) }}>
    <span class="tw:rounded-full {{ $colorClass }}" style="{{ $sizeStyle }} display: inline-block;"></span>
    @if($label)
        <span>{{ $label }}</span>
    @endif
</span>
