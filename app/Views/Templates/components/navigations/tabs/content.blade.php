@props([
    'id', 
    'ariaLabel',
    'classExtra' => '',
    'checked' => ''
])

@aware(['name'])

<input type="radio" name="{{ $name }}" id="{{ $id }}" role="tab" class="tab" aria-label="{{ $ariaLabel }}" {{ $checked ? 'checked="checked"' : '' }} />
<div 
    role="tabpanel" 
    {{ $attributes->merge(['id' => $id, 'class' => 'tab-content ' . $classExtra]) }}
>
    {{ $slot }}
</div>