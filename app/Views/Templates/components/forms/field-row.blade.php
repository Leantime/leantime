@props([
    'labelPosition' => '',
    'labelText' => '',
    "helpText" => '',
    "validationText" => '',
    "validationClass" => '',
    "validationText" => '',
    'caption' => ''
])

<div {{ $attributes->merge(['class'=> 'relative md:flex md:items-center mb-1' ]) }}>

    @if($labelPosition == 'left')
        <div class="md:w-1/3">
            @if($labelText)
                <span {{ $attributes->merge(['class'=> 'label-text' ]) }}>{{ $labelText }}</span>
            @endif
        </div>
    @endif

    @if($labelPosition == 'left' || $labelPosition == 'right')
        <div class="md:w-2/3">
    @else
        <div class="w-full">
    @endif

        <x-global::forms.label-row>
            @if($labelText && $labelPosition !== 'left')
                <x-slot:label-text> {!! $labelText !!}</x-slot:label-text>
            @endif
            @if($helpText)
                <x-slot:help-text> {!! $helpText !!}</x-slot:help-text>
            @endif
        </x-global::forms.label-row>

        @if($caption)
            <span class="label-text">{{ $caption }}</span>
        @endif

        {{ $slot }}

        @if($validationText)
            <x-global::forms.label-row class="mt-1 transition-opacity duration-500 ease-in-out opacity-100">
                <x-slot:label-text-right class="{{ $validationClass }}"> {!! $validationText !!}</x-slot:label-text-right>
            </x-global::forms.label-row>
        @endif
    </div>
    @if($labelPosition == 'right')
        <div class="md:w-1/3">
            @if($labelText)
                <span {{ $attributes->merge(['class'=> 'label-text font-medium' ]) }}>{{ $labelText }}</span>
            @endif
        </div>
    @endif
</div>

