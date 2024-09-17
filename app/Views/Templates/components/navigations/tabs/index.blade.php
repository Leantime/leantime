@props([
    'contents',
    'variant' => '',
    'tabsSize' => ''
])

@php 
    $variantClass = $variant ? 'tabs-'.$variant : '';
    $sizeClass = $tabsSize && $tabsSize !== 'md' ? 'tabs-'.$tabsSize : '';
@endphp

<div role="tablist" {{ $attributes->merge(['class' => 'tabs '.$variantClass.' '.$sizeClass]) }}>
    {{ $contents }}
</div>