@props([
    'value' => 0,
    'max' => 100,
    'label' => '',
    'color' => 'primary',
    'state' => null,
    'size' => 'md',
    'scale' => null,
    'showLabel' => false,
])

@php
    $resolvedColor = $state ?? $color;
    $resolvedSize = $scale ?? $size;
    $percent = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;

    $sizeClass = match($resolvedSize) {
        's', 'sm' => 'tw:h-1.5',
        'l', 'lg' => 'tw:h-4',
        default => 'tw:h-2.5',
    };

    $colorClass = match($resolvedColor) {
        'success' => 'tw:bg-success',
        'warning' => 'tw:bg-warning',
        'error', 'danger' => 'tw:bg-error',
        'info'    => 'tw:bg-info',
        default   => 'tw:bg-primary',
    };

    $displayLabel = $label ?: sprintf('%d%%', $percent);
@endphp

<div {{ $attributes->merge(['class' => 'progress-wrapper']) }}
     role="progressbar"
     aria-valuenow="{{ $value }}"
     aria-valuemin="0"
     aria-valuemax="{{ $max }}"
     aria-label="{{ $displayLabel }}">

    @if($showLabel)
        <div class="tw:flex tw:justify-between tw:mb-1">
            <span class="tw:text-sm tw:font-medium">{{ $label }}</span>
            <span class="tw:text-sm tw:font-medium">{{ $percent }}%</span>
        </div>
    @endif

    <div class="tw:w-full tw:bg-base-200 tw:rounded-full {{ $sizeClass }} tw:overflow-hidden">
        <div class="{{ $colorClass }} {{ $sizeClass }} tw:rounded-full tw:transition-all tw:duration-300"
             style="width: {{ $percent }}%">
        </div>
    </div>

    <span class="tw:sr-only">{{ $displayLabel }}</span>
</div>
