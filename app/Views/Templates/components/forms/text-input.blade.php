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
])

@php
    $sizeClass = $size ? 'input-'.$size : '';
    $stateClass = $state ? 'input-'.$state : '';
    $validationClass = $validationState ? 'text-yellow-500' : '';
@endphp

<div class='par relative w-full max-w-xs'>
    <x-global::forms.label-row>
        @if($labelText)
            <x-slot:label-text> {!! $labelText !!}</x-slot:label-text>
        @endif
        @if($labelRight)
            <x-slot:label-right> {!! $labelRight !!}</x-slot:label-right>
        @endif
    </x-global::forms.label-row>

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <div class="relative">

        <x-global::elements.leadingVisual>
            {{ $leadingVisual }}
        </x-global::elements.leadingVisual>

        <input {{$attributes->merge(['class' => 'input input-shadow input-bordered '.$sizeClass.' '.$stateClass.' w-full max-w-xs '.($leadingVisual ? 'pl-10' : '').($trailingVisual ? 'pr-10' : '')])}}
        />

        <x-global::elements.trailingVisual>
            {{ $trailingVisual }}
        </x-global::elements.trailingVisual>
    </div>

    @if($validationText)
        <x-global::forms.label-row class="mt-1 transition-opacity duration-500 ease-in-out opacity-100">
            <x-slot:label-text-right class="{{ $validationClass }}"> {!! $validationText !!}</x-slot:label-text-right>
        </x-global::forms.label-row>
    @endif

</div>
