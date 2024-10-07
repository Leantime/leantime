@props([
    'variant' => 'horizontal', //horizontal, vertical
    'classExtra' => '',
    'contents',
])

@php 
    $variantClass = $variant === "vertical" ? 'steps-'.$variant : '';
@endphp

<ul {{ $attributes->merge(['class' => 'steps '.$variantClass.' '.$classExtra]) }}>
    {{ $contents }}
</ul>