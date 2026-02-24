{{-- Backward-compat wrapper: maps old API → elements.badge naming-doc API --}}
@props([
    'asLink' => false,
    'color' => 'gray',
    'size' => null,
    'icon' => null,
    'bgColor' => null,
    'dot' => false,
])

<x-globals::elements.badge
    :as-link="$asLink"
    :state="$color"
    :scale="$size"
    :icon="$icon"
    :bg-color="$bgColor"
    :dot="$dot"
    {{ $attributes }}
>{{ $slot }}</x-globals::elements.badge>
