@props([
    'options' => [],
    'labelText' => '',
    'labelRight' => '',
    'caption' => '',
    'captionState' => '', 
    // 'validationText' => '',
    // 'validationState' => '',
    'leadingVisual' => '',
    'size' => '',
    'state' => '',
    'variant' => 'single',
    'selected' => [],
])

@php
    $sizeClass = $size && $size != 'md' ? 'select-'.$size : '';
    $stateClass = $state && $state != 'disabled' ? 'select-'.$state : '';
    $captionClass = $captionState ? 'text-'.$captionState : '';
@endphp


<div>
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

    <div>
        @if($leadingVisual)
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {{ $leadingVisual }}>
            </span>
        @endif

        <select  
            {{$attributes->merge(['class' => 'select select-bordered '.$sizeClass.' '.$stateClass.' w-full max-w-xs '.($leadingVisual ? 'pl-10' : '')])}}
            {{ $state === 'disabled' ? 'disabled' : '' }}
            {{-- {{ $variant === 'multiple' || $variant === 'tags' ? 'multiple' : '' }} --}}
        >
            @foreach($options as $value => $label)
                <option value="{{ $value }}" {{ in_array($value, (array)$selected) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

    </div>

    @if($caption)
        <label>
            <span class="label-text-alt {{ $captionClass }}">{{ $caption }}</span>
        </label>
    @endif

    {{-- @if($validationText)
        <label>
            <span class="label-text-alt text-{{ $validationState }}">{{ $validationText }}</span>
        </label>
    @endif --}}
</div>