{{-- Backward-compat wrapper: maps old API → feedback.progress naming-doc API --}}
@props([
    'value' => 0,
    'max' => 100,
    'label' => '',
    'color' => 'primary',
    'size' => 'md',
    'showLabel' => false,
])

<x-globals::feedback.progress
    :value="$value"
    :max="$max"
    :label="$label"
    :state="$color"
    :scale="$size"
    :show-label="$showLabel"
    {{ $attributes }}
/>
