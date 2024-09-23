@props([
    'inputType' => 'text',
    'labelText' => '',
    'labelRight' => '',
    'size' => '',
    'state' => '',
    'leadingVisual' => '',
    'trailingVisual' => '',
    'caption' => '',
    'validationText' => '',
    'validationState' => '',
    'variant' => '',
])

@php

    $stateClass = $state ? 'input-'.$state : '';
    $validationClass = $validationState ? 'text-red-500' : '';
    $ghostClass = $variant === 'ghost' ? 'input-ghost' : '';

    if ($variant === 'title') {
        $style= 'text-lg';
        $width = 'max-w-full';
        $sizeClass = 'w-full';
        $ghostClass = 'input-ghost ';

    } elseif ($variant === 'compact') {
        $style= 'input-bordered input-sm';
        $width = 'max-w-xs';
        $sizeClass = '';

    } else {
        $style= 'input-bordered  input-sm';
        $width = 'w-full max-w-xs';
        $sizeClass = $size ? 'input-'.$size : '';
    }
@endphp

<div class='par relative form-control {{$width}}'>
    <div>
        @if($labelText)
        <span class='label-text font-medium'>{!! $labelText !!}</span>
        @endif
        @if($labelRight)
        <span class='label-text-alt'>{!! $labelRight !!}</span>
        @endif
    </div>

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <div class="relative">
        @if($leadingVisual)
            <x-global::elements.leadingVisual>
                {{ $leadingVisual }}
            </x-global::elements.leadingVisual>
        @endif

        <input {{$attributes->merge(['class' => 'input input-hover '.$style.' '.$sizeClass.' '.$stateClass.' '.$width.' '.$ghostClass.' '.($leadingVisual ? 'pl-10' : '').($trailingVisual ? 'pr-10' : '')])}} />

        @if($trailingVisual)
            <x-global::elements.trailingVisual>
                {{ $trailingVisual }}
            </x-global::elements.trailingVisual>
        @endif
    </div>

    @if($validationText)
        <x-global::forms.label-row class="mt-1 transition-opacity duration-500 ease-in-out opacity-100">
            <x-slot:label-text-right class="{{ $validationClass }}"> {!! $validationText !!}</x-slot:label-text-right>
        </x-global::forms.label-row>
    @endif
</div>
