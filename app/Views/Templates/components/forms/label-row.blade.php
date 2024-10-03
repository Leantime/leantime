@props([
   'labelText' => false,
   'helpText' => false
])

@if($labelText || $helpText)
    <div {{ $attributes->merge(['class'=> 'label' ]) }}>
        @if($labelText)
            <span {{ $attributes->merge(['class'=> 'label-text' ]) }}>{{ $labelText }}</span>
        @endif
        @if($helpText)
            <span {{ $attributes->merge(['class'=> 'label-text-alt' ]) }}>{{ $helpText }}</span>
        @endif
    </div>
@endif
