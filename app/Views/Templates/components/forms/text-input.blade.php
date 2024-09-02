@props([
    'inputType' => 'text',
    'id' => '',
    'name' => '',
    'placeholder' => '',
    'label' => '',
    'size' => '',
    'inputState' => '',
    'value' => '',
    'leadingVisual' => '',
    'trailingVisual' => '',
])

@php
    $sizeClass = $size ? 'input-'.$size : '';
    $stateClass = $inputState ? 'input-'.$inputState : '';
@endphp

<div class='par relative w-full max-w-xs'>
    @if($label)
        <label for="{{ $id }}">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif

    <div class="relative">
        @if($leadingVisual)
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="{{ $leadingVisual }}"></i>
            </span>
        @endif

        <input 
            type="{{ $inputType }}" 
            id="{{ $id }}" 
            name="{{ $name }}" 
            placeholder="{{ $placeholder }}" 
            value="{{ $value }}" 
            {{$attributes->merge(['class' => 'input input-bordered '.$sizeClass.' '.$stateClass.' '.'w-full max-w-xs '.($leadingVisual ? 'pl-10' : '').($trailingVisual ? 'pr-10' : '')])}}
        />

        @if($trailingVisual)
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="{{ $trailingVisual }}"></i>
            </span>
        @endif
    </div>
</div>