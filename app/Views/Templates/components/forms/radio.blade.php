@props ([
    'labelText' => '',
    'size' => '',
    'state' => '',
    'color' => '',      // e.g. bg-red-500
])

@php
    $sizeClass = $size ? 'radio-'.$size : '';
    $stateClass = $state ? 'radio-'.$state : '';
    $colorClass = $color ? 'checked:'.$color : '';
@endphp

@if ($labelText)
    <div class="form-control">
        <label class="cursor-pointer label">
            <span class="label-text">{{$labelText}}</span>
            <input 
                type="radio" 
                {{ $attributes->merge(['class' => 'radio '.$sizeClass.' '.$stateClass]) }}
            />
        </label>
    </div>
@else
    <input 
        type="radio" 
        {{ $attributes->merge(['class' => 'radio '.$sizeClass.' '.$stateClass.' '.$colorClass]) }}
    />
@endif