{{-- Backward-compat wrapper: maps old API → forms.button-group --}}
@props([
    'vertical' => false,
])

<x-globals::forms.button-group :vertical="$vertical" {{ $attributes }}>{{ $slot }}</x-globals::forms.button-group>
