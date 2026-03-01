@props([
    'name' => '',
    'size' => null,
    'weight' => null,
    'fill' => false,
])

@php
    $sizeStyle = match($size) {
        'xs' => 'font-size: 14px;',
        'sm', 's' => 'font-size: 18px;',
        'md', 'm' => 'font-size: 24px;',
        'lg', 'l' => 'font-size: 32px;',
        'xl' => 'font-size: 40px;',
        default => '',
    };

    $variableStyles = collect([
        $weight ? "'wght' {$weight}" : null,
        $fill ? "'FILL' 1" : null,
    ])->filter()->implode(', ');

    $fontVariation = $variableStyles
        ? "font-variation-settings: {$variableStyles};"
        : '';

    $inlineStyle = trim("{$sizeStyle} {$fontVariation}");
@endphp

<span {{ $attributes->merge([
    'class' => 'material-symbols-outlined',
    'aria-hidden' => 'true',
] + ($inlineStyle ? ['style' => $inlineStyle] : [])) }}>{{ $name }}</span>
