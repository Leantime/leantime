{{-- Backward-compat wrapper: maps old API → elements.avatar naming-doc API --}}
@props([
    'userId' => null,
    'username' => '',
    'size' => 'md',
])

<x-globals::elements.avatar
    :user-id="$userId"
    :username="$username"
    :scale="$size"
    {{ $attributes }}
/>
