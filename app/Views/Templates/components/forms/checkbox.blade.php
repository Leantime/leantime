@props ([
    'labelText' => '',
    'size' => '',
    'state' => ''
])

@php
    $sizeClass = $size ? 'checkbox-'.$size : '';
    $stateClass = $state ? 'checkbox-'.$state : '';
@endphp

@if ($labelText)
        <label class="cursor-pointer label">
            <input 
                type="checkbox" 
                {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
            />
            <span class="label-text">{{$labelText}}</span>
        </label>
@else
    <input 
        type="checkbox" 
        {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
    />
@endif