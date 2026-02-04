@props([
    'id',
    'ariaLabel',
    'classExtra' => '',
    'checked' => ''
])

@aware(['name'])

<div role="tabpanel" {{ $attributes->merge(['id' => $name, 'class' => 'tab-content ' . $classExtra]) }}>
    {{ $slot }}
</div>
