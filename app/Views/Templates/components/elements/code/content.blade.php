@props([
    'prefix' => '',
    'textColor' => '', // primary, secondary, info, success, warning, error
    'highlightColor' => '' // primary, secondary, info, success, warning, error
])

@php 
    $textColorClass = $textColor ? 'text-'.$textColor : '';
    $highlightColorClass = $highlightColor ? 'bg-'.$highlightColor.' text-'.$highlightColor.'-content' : ''
@endphp

<pre {{ $attributes->merge(['data-prefix' => $prefix, 'class' => $textColorClass.' '.$highlightColorClass]) }}>
    <code>
        {{$slot}}
    </code>
</pre>