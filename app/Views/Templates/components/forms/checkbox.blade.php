@props ([
    'labelText' => '',
    'size' => '',
    'state' => '',
    'checked' => '',
    'disabledState' => ''
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
                {{ $checked === "checked" ? "checked='checked'" : "" }} 
                {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
                {{ $disabledState === 'disabled' ? 'disabled' : '' }}
            />
        </label>
    </div>
@else
    <input 
        type="checkbox" 
        {{ $checked === "checked" ? "checked='checked'" : "" }} 
        {{ $attributes->merge(['class' => 'checkbox '.$sizeClass.' '.$stateClass]) }}
        {{ $disabledState === 'disabled' ? 'disabled' : '' }}
    />
@endif