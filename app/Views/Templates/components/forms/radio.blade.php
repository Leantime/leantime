@props ([
    'labelText' => '',
    'size' => '',
    'state' => '',
    'checked' => '',    //'checked' or keep empty
    'color' => '',      // e.g. bg-red-500
    'disabledState' => ''   //'disabled' or keep empty
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
                {{ $checked === "checked" ? "checked='checked'" : "" }} 
                {{ $attributes->merge(['class' => 'radio '.$sizeClass.' '.$stateClass]) }}
                {{ $disabledState === 'disabled' ? 'disabled' : '' }}
            />
        </label>
    </div>
@else
    <input 
        type="radio" 
        {{ $checked === "checked" ? "checked='checked'" : "" }} 
        {{ $attributes->merge(['class' => 'radio '.$sizeClass.' '.$stateClass.' '.$colorClass]) }}
        {{ $disabledState === 'disabled' ? 'disabled' : '' }}
    />
@endif