@props([
    'name',
    'contents',
    'variant' => '',
    'size' => ''
])

@php 
    $variantClass = $variant ? 'tabs-'.$variant : '';
    $sizeClass = $size && $size !== 'md' ? 'tabs-'.$size : '';
@endphp

<div role="tablist" {{ $attributes->merge(['class' => 'tabs '.$variantClass.' '.$sizeClass]) }}>
    {{ $contents->withAttributes(['name' => $name]) }}
</div>