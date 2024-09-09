@props ([
    'labelText' => '',
    'size' => '',
    'state' => '',
])

@php
    $sizeClass = $size ? 'checkbox-'.$size : '';
    $stateClass = $state ? 'checkbox-'.$state : '';
@endphp

@if ($labelText)
    <div class="form-control">
        <label class="cursor-pointer label">
            <span class="label-text">{{$labelText}}</span>
            <input 
                type="checkbox" 
                {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
            />
        </label>
    </div>
@else
    <input 
        type="checkbox" 
        {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
    />
@endif