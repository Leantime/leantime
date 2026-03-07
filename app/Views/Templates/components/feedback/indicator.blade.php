@props([
    'status' => 'default',
    'state' => null,
    'label' => null,
    'size' => 'sm',
    'scale' => null,
])

@php
    $resolvedStatus = $state ?? $status;
    $resolvedSize = $scale ?? $size;

    $colorClass = match($resolvedStatus) {
        'success', 'green'  => 'tw:bg-success',
        'warning', 'yellow' => 'tw:bg-warning',
        'error', 'danger', 'red' => 'tw:bg-error',
        'info', 'blue'      => 'tw:bg-info',
        default              => 'tw:bg-neutral',
    };
    $sizeStyle = match($resolvedSize) {
        'xs' => 'width: 6px; height: 6px;',
        's', 'sm' => 'width: 8px; height: 8px;',
        'm', 'md' => 'width: 10px; height: 10px;',
        'l', 'lg' => 'width: 14px; height: 14px;',
        default => 'width: 8px; height: 8px;',
    };
@endphp

<span {{ $attributes->merge(['class' => 'tw:inline-flex tw:items-center tw:gap-1.5']) }}>
    <span class="tw:rounded-full {{ $colorClass }}" style="{{ $sizeStyle }} display: inline-block;"></span>
    @if($label)
        <span>{{ $label }}</span>
    @endif
</span>
