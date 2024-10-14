@props([
    'activeState' => false,
    'disabledState' => false,
    'labelText' => '',
])

@aware(['inputType', 'outline', 'scale'])

@php
    $outlineClass = $outline ? 'btn-outline' : '';
    $activeClass = $activeState ? 'btn-active' : '';
    $disabledClass = $disabledState ? 'btn-disabled' : '';
    
    $scaleClass = '';

    if ($scale === "xs") {
        $scaleClass = "btn-xs";
    } elseif ($scale === "s") {
        $scaleClass = "btn-sm";
    } elseif ($scale === "m") {
        $scaleClass = "btn-md";
    } elseif ($scale === "l") {
        $scaleClass = "btn-lg";
    }
@endphp


@if ($inputType === "radio")
    <input
        {{ $attributes->merge([
            'class' => 'join-item btn',
            'type' => 'radio',
            'aria-label' => $labelText
        ]) }}
    />
@else
    <button {{ $attributes->merge(['class' => 'join-item btn '.$scaleClass.' '.$outlineClass.' '.$activeClass.' '.$disabledClass]) }}>
        {{ $slot }}
    </button>
@endif