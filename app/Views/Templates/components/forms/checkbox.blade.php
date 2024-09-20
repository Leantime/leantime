@props ([
    'labelText' => '',
    'labelPosition' => 'left', //left, right
    'size' => '',
    'state' => ''
])

@php
    $sizeClass = $size ? 'checkbox-'.$size : '';
    $stateClass = $state ? 'checkbox-'.$state : '';
@endphp

@if ($labelText)
        <label class="cursor-pointer label">
            @if ($labelPosition === 'left')
                <span class="label-text mr-1">{{$labelText}}</span>
            @endif
            <input 
                type="checkbox" 
                {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
            />
            @if ($labelPosition === 'right')
                <span class="label-text ml-1">{{$labelText}}</span>
            @endif
        </label>
@else
    <input 
        type="checkbox" 
        {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
    />
@endif