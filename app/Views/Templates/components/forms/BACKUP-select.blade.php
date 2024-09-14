@props([
    'labelText' => '',
    'labelRight' => '',
    'caption' => '',
    'leadingVisual' => '',
    'size' => '',
    'state' => '',
    'variant' => 'single',
    'validationText' => '',
    'validationState' => '',
])

@php
    $sizeClass = $size && $size != 'md' ? 'select-'.$size : '';
    $stateClass = $state && $state != 'disabled' ? 'select-'.$state : '';
    $validationClass = $validationState ? 'text-yellow-500' : '';
@endphp


<div class='relative w-full max-w-xs'>
    @if($labelText || $labelRight)
        <div class="flex justify-between items-center">
            @if($labelText)
                <label for="{{ $attributes->get('id') }}">
                    <span class="label-text">{{ $labelText }}</span>
                </label>
            @endif
            @if($labelRight)
                <label>
                    <span class="label-text-alt">{{ $labelRight }}</span>
                </label>
            @endif
        </div>
    @endif

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <div>
        @if($leadingVisual)
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {{ $leadingVisual }}>
            </span>
        @endif

        <select  
            {{$attributes->merge(['class' => 'select select-bordered '.$sizeClass.' '.$stateClass.' w-full max-w-xs '.($leadingVisual ? 'pl-10' : '')])}}
            {{ $state === 'disabled' ? 'disabled' : '' }}
            {{ $variant === 'multiple' || $variant === 'tags' ? 'multiple' : '' }}
        >
            {{ $slot }}
        </select>

    </div>


    @if($validationText)
        <div class="mt-1 transition-opacity duration-500 ease-in-out opacity-100">
            <p class="text-sm {{ $validationClass }}">{{ $validationText }}</p>
        </div>
    @endif

</div>

