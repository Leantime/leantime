@props([
    'title' => null,
    'compact' => false,
    'bordered' => false,
    'flush' => false,
    'glass' => false,
    'contentRole' => null,
    'state' => null,
    'scale' => null,
    'variant' => null,
])

@php
    // Naming-doc aliases: variant maps to compact/bordered/flush/glass
    $isCompact = $compact || $scale === 's' || $variant === 'compact';
    $isBordered = $bordered || $variant === 'bordered';
    $isGlass = $glass || $variant === 'glass';

    $cardClasses = 'tw:card tw:bg-base-100 tw:shadow-sm'
        . ($isCompact ? ' tw:card-sm' : '')
        . ($isBordered ? ' tw:border tw:border-base-300' : '')
        . ($isGlass ? ' lt-glass' : '');
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @if(isset($header) || isset($headerActions))
        <div class="tw:card-header tw:flex tw:items-center tw:justify-between tw:px-4 tw:py-3 tw:border-b tw:border-base-200">
            <div>
                @isset($header)
                    {{ $header }}
                @else
                    @if($title)
                        <h2 class="tw:card-title">{{ $title }}</h2>
                    @endif
                @endisset
            </div>
            @isset($headerActions)
                <div class="tw:flex tw:items-center tw:gap-2">
                    {{ $headerActions }}
                </div>
            @endisset
        </div>
    @endif
    <div class="{{ $flush ? '' : 'tw:card-body' }}">
        @if(!isset($header) && !isset($headerActions) && $title)
            <h2 class="tw:card-title">{{ $title }}</h2>
        @endif
        {{ $slot }}
        @isset($actions)
            <div class="tw:card-actions tw:justify-end">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
