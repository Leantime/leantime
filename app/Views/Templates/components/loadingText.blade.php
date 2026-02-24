{{-- Backward-compat wrapper: maps old API → feedback.skeleton --}}
@props([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text'
])

<x-globals::feedback.skeleton :count="$count" :include-headline="$includeHeadline" :type="$type" {{ $attributes }} />
