@props([
    'size' => 'md',
    'scale' => null,
])

@php
    $resolvedSize = $scale ?? $size;
@endphp

<span {{ $attributes->merge(['class' => 'htmx-indicator', 'role' => 'status', 'aria-live' => 'polite']) }}>
    <span class="indeterminate"></span>
    <span class="sr-only">{{ __('label.loading') }}</span>
</span>
