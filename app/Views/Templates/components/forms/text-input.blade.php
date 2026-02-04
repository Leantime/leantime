@props([
    //Basic Definition
    'contentRole' => '', //default, primary, secondary, tertiary (ghost), accent, link
    'state' => '', //default, info, warning, danger, success,
    'scale' => '',

    //labels & content
    'labelPosition' => 'top',
    'labelText' => '',
    'helpText' => '',
    'leadingVisual' => '',
    'trailingVisual' => '',
    'caption' => '',
    'validationText' => '',
    'validationState' => '',
    'modalTitle' => '',
    //Variation options
    'variant' => '', //default, title, compact, fullWidth, noBorder
])

@php

    $stateClass = $state ? 'input-'.$state : '';
    $validationClass = $validationState ? 'text-red-500' : '';
    $ghostClass = $variant === 'ghost' ? 'input-ghost' : '';

    if ($variant === 'title') {

        $style= 'input-bordered border-b border-t-0 border-l-0 border-r-0 rounded-none text-2xl p-0 ml-[-5px] pl-[5px] hover:(input-hover rounded-sm) mb-4';
        $width = 'max-w-full';
        $sizeClass = 'w-full';
        $ghostClass = ' ';

    } elseif ($variant === 'compact') {

        $style= 'input-bordered input-sm';
        $width = 'max-w-xs';
        $sizeClass = $scale ? 'input-'.$size : '';

    } elseif ($variant === 'fullWidth') {

        $style= 'input-bordered  input-sm';
        $width = 'w-full';
        $sizeClass = $scale ? 'input-'.$scale : '';

    } elseif ($variant === 'noBorder') {

        $style= 'input-md';
        $width = 'w-full';
        $sizeClass = $scale ? 'input-'.$scale : '';

    } else {

        $style= 'input-bordered  input-sm';
        $width = 'w-full';
        $sizeClass = $scale ? 'input-'.$scale : '';
    }
@endphp

<x-global::forms.field-row :label-position="$labelPosition" class="{{$width}}">
    @if($labelText)
        <x-slot:label-text> {!! $labelText !!}</x-slot:label-text>
    @endif

    @if($helpText)
        <x-slot:help-text> {!! $helpText !!}</x-slot:help-text>
    @endif

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <div class="relative">
        @if($leadingVisual)
            <x-global::elements.leadingVisual>
                {{ $leadingVisual }}
            </x-global::elements.leadingVisual>
        @endif

        <input {{$attributes->merge(['class' => 'input '.$style.' '.$sizeClass.' '.$stateClass.' '.$width.' '.$ghostClass.' '.($leadingVisual ? 'pl-10' : '').($trailingVisual ? 'pr-10' : '')])}} />

        @if($trailingVisual)
            <x-global::elements.trailingVisual>
                {{ $trailingVisual }}
            </x-global::elements.trailingVisual>
        @endif
    </div>

    @if($validationText)
        <x-slot:validation-text> {!! $validationText !!}</x-slot:validation-text>
    @endif

</x-global::forms.field-row>

