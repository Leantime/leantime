@props([
    'name', 
    'ariaLabel',
    'active' => false,
    'classExtra' => ''
])

<input type="radio" name="{{ $name }}" id="{{ $name }}" role="tab" class="tab" aria-label="{{ $ariaLabel }}" {{ $active ? 'checked' : '' }} />
<div 
    role="tabpanel" 
    {{ $attributes->merge(['id' => $name, 'class' => 'tab-content ' . $classExtra]) }}
>
    {{ $slot }}
</div>