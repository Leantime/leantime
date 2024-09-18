@props([
    'contents',
    'variant' => '',
    'tabsSize' => '',
    'name' => '',
])

@php
    $variantClass = $variant ? 'tabs-'.$variant : '';
    $sizeClass = $tabsSize && $tabsSize !== 'md' ? 'tabs-'.$tabsSize : '';
@endphp

<div role="tablist" {{ $attributes->merge(['class' => 'tabs tabs-bordered '.$variantClass.' '.$sizeClass]) }}>
    {{ $contents }}
</div>
