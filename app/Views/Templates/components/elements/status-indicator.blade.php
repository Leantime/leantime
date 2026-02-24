{{-- Backward-compat wrapper: maps old API → feedback.indicator naming-doc API --}}
@props([
    'status' => 'default',
    'label' => null,
    'size' => 'sm',
])

<x-globals::feedback.indicator
    :state="$status"
    :label="$label"
    :scale="$size"
    {{ $attributes }}
/>
