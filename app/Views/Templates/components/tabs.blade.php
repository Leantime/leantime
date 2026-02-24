{{-- Backward-compat wrapper: maps old API → navigations.tabs --}}
@props([
    'headings',
    'contents',
])

<x-globals::navigations.tabs {{ $attributes }}>
    <x-slot:headings {{ $headings->attributes }}>{{ $headings }}</x-slot:headings>
    <x-slot:contents>{{ $contents }}</x-slot:contents>
</x-globals::navigations.tabs>
