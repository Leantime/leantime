@props([
    'size' => 'md',
])

<span {{ $attributes->merge(['class' => 'htmx-indicator']) }}>
    <span class="indeterminate"></span>
</span>
