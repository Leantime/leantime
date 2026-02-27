{{-- Backward-compat wrapper: maps old API -> feedback.progress naming-doc API --}}
@props([
    'value' => 0,
    'max' => 100,
    'label' => '',
    'color' => 'primary',
    'customColor' => null,
    'size' => 'md',
    'showLabel' => true,
])

<x-globals::feedback.progress
    :value="$value"
    :max="$max"
    :label="$label"
    :state="$color"
    :custom-color="$customColor"
    :scale="$size"
    :show-label="$showLabel"
    {{ $attributes }}
/>
