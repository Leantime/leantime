{{-- Backward-compat wrapper: maps old API → feedback.loading naming-doc API --}}
@props([
    'size' => 'md',
])

<x-globals::feedback.loading :scale="$size" {{ $attributes }} />
