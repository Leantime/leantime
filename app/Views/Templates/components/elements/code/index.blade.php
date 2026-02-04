@props([
    'contents',
    'bgColor' => '', // primary, secondary, info, success, warning, error
    'textColor' => '' // primary, secondary, info, success, warning, error
])

@php 
    $bgColorClass = $bgColor ? 'bg-'.$bgColor : '';
    $textColorClass = $textColor ? 'text-'.$textColor.'-content' : '';
@endphp

<div {{ $attributes->merge(['class' => 'mockup-code '.$bgColorClass.' '.$textColorClass]) }}>
    {{ $contents }}
</div>