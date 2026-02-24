@props([
    'size' => 'md',
    'scale' => null,
])

@php
    $resolvedSize = $scale ?? $size;
@endphp

<span {{ $attributes->merge(['class' => 'htmx-indicator']) }}>
    <span class="indeterminate"></span>
</span>
