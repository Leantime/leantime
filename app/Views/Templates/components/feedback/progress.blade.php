@props([
    'value' => 0,
    'max' => 100,
    'label' => '',
    'color' => 'primary',
    'customColor' => null,
    'state' => null,
    'size' => 'md',
    'scale' => null,
    'showLabel' => true,
])

@php
    $resolvedColor = $state ?? $color;
    $resolvedSize = $scale ?? $size;
    $percent = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;

    // Force showLabel false for sm size
    $showText = ($resolvedSize === 'sm' || $resolvedSize === 's') ? false : $showLabel;

    // Build label text
    $labelText = $label !== '' ? $label . ': ' . $percent . '%' : $percent . '%';

    // Size dimensions
    $trackHeight = match($resolvedSize) {
        's', 'sm' => '8px',
        'l', 'lg' => '32px',
        default => '24px',
    };

    $fontSize = match($resolvedSize) {
        'l', 'lg' => 'var(--font-size-s)',
        default => 'var(--font-size-xs)',
    };

    // Color mapping
    if ($customColor) {
        $accentColor = $customColor;
    } else {
        $accentColor = match($resolvedColor) {
            'success' => 'var(--feedback-success-color)',
            'warning' => 'var(--feedback-warning-color)',
            'error', 'danger' => 'var(--feedback-error-color)',
            'info' => 'var(--feedback-info-color)',
            default => 'var(--accent1)',
        };
    }
@endphp

<div {{ $attributes->merge(['class' => 'emboss-progress']) }}
     role="progressbar"
     aria-valuenow="{{ $value }}"
     aria-valuemin="0"
     aria-valuemax="{{ $max }}"
     aria-label="{{ $labelText }}"
     style="width: 100%;">

    {{-- Track: recessed well --}}
    <div style="
        position: relative;
        height: {{ $trackHeight }};
        border-radius: 999px;
        background: color-mix(in srgb, {{ $accentColor }} 15%, var(--secondary-background, #e8e8e8));
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.18), inset 0 1px 2px rgba(0,0,0,0.12), 0 1px 0 rgba(255,255,255,0.5);
        overflow: hidden;
    ">
        {{-- Fill: embossed glossy bar --}}
        <div style="
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: {{ $percent }}%;
            border-radius: 999px;
            background: linear-gradient(to bottom, rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.15) 40%, rgba(255,255,255,0) 50%, rgba(0,0,0,0.08) 60%, rgba(0,0,0,0.18) 100%), {{ $accentColor }};
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.35), inset 0 -1px 1px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.1);
            transition: width 0.3s ease;
        "></div>

        {{-- Label: centered on track --}}
        @if($showText)
            <span style="
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 500;
                font-size: {{ $fontSize }};
                letter-spacing: 0.01em;
                color: var(--kanban-col-title-color, var(--primary-font-color));
                white-space: nowrap;
                z-index: 1;
                line-height: 1;
                pointer-events: none;
            ">{{ $labelText }}</span>
        @endif
    </div>

    <span class="tw:sr-only">{{ $labelText }}</span>
</div>
