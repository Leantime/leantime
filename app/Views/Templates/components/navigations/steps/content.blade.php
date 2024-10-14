@props([
    'contentRole' => 'primary', //primary, secondary, accent 
    'data' => null
])

@php
    $contentClass = $contentRole ? 'step-'.$contentRole : '';
    $attributes = $attributes->class(['step '.$contentClass]);
    
    if ($data !== null) {
        $attributes = $attributes->merge(['data-content' => $data]);
    }
@endphp

<li {{ $attributes }} >
    {{ $slot }}
</li>