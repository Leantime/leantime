@props([
    'variant' => 'light',
    'icon' => null,
    'borderColor' => null,
    'tag' => 'h4',
])

@php
    $classes = ['widgettitle'];

    if ($variant === 'light') {
        $classes[] = 'title-light';
    } elseif ($variant === 'primary') {
        $classes[] = 'title-primary';
    }
    // variant="plain" → no additional modifier class

    if ($borderColor) {
        $classes[] = 'title-border-' . $borderColor;
    }
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    @if ($icon)
        <x-globals::elements.icon :name="$icon" />
    @endif
    {{ $slot }}
</{{ $tag }}>
