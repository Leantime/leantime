{{-- Backward-compat wrapper: maps old API → layout.page-header naming-doc API --}}
@props([
    'icon' => 'home',
    'headline' => null,
    'subtitle' => null,
])

<x-globals::layout.page-header
    :leading-visual="$icon"
    :headline="$headline"
    :subtitle="$subtitle"
    {{ $attributes }}
>
    @if($headline === null)
        {{ $slot }}
    @endif
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-globals::layout.page-header>
