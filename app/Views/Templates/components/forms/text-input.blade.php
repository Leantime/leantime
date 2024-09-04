@props([
    'inputType' => 'text',
    'labelText' => '',
    'labelRight' => '',
    'size' => '',
    'state' => '',
    'leadingVisual' => '',
    'trailingVisual' => '',
    'caption' => '', 
    'captionState' => '', 
])

@php
    $sizeClass = $size ? 'input-'.$size : '';
    $stateClass = $state ? 'input-'.$state : '';
    $captionClass = $captionState ? 'text-'.$captionState : '';
@endphp

<div class='par relative w-full max-w-xs'>
    @if($labelText || $labelRight)
        <div class="flex justify-between">
            @if($labelText)
                <label for="{{ $attributes->get('id') }}">
                    <span class="label-text">{{ $labelText }}</span>
                </label>
            @endif
            @if($labelRight)
                <span class="label-text-alt">{{ $labelRight }}</span>
            @endif
        </div>
    @endif

    <div class="relative">
        @if($leadingVisual)
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {{ $leadingVisual }}
            </span>
        @endif

        <input 
            {{$attributes->merge(['class' => 'input input-bordered '.$sizeClass.' '.$stateClass.' w-full max-w-xs '.($leadingVisual ? 'pl-10' : '').($trailingVisual ? 'pr-10' : '')])}}
        />

        @if($trailingVisual)
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                {{ $trailingVisual }}
            </span>
        @endif
    </div>

    @if($caption)
        <span class="label-text-alt {{ $captionClass }}">{{ $caption }}</span>
    @endif
</div>
