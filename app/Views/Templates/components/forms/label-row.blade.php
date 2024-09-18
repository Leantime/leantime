@props([
   'labelText' => false,
   'labelRight' => false
])

@if($labelText || $labelRight)
    <div {{ $attributes->merge(['class'=> 'label' ]) }}>
        @if($labelText)
            <span {{ $attributes->merge(['class'=> 'label-text font-medium' ]) }}>{{ $labelText }}</span>
        @endif
        @if($labelRight)
            <span {{ $attributes->merge(['class'=> 'label-text-alt' ]) }}>{{ $labelRight }}</span>
        @endif
    </div>
@endif
