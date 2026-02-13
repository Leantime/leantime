@props([
    'link' => '#',
    'type' => 'primary',
    'tag'  => 'a',
    'size' => null,
    'outline' => false,
])

@php
    $typeClass = match($type) {
        'primary'   => 'tw:btn-primary',
        'secondary' => 'tw:btn-secondary',
        'accent'    => 'tw:btn-accent',
        'info'      => 'tw:btn-info',
        'success'   => 'tw:btn-success',
        'warning'   => 'tw:btn-warning',
        'error'     => 'tw:btn-error',
        'ghost'     => 'tw:btn-ghost',
        'link'      => 'tw:btn-link',
        default     => 'tw:btn-primary',
    };
    $sizeClass = match($size) {
        'xs' => 'tw:btn-xs',
        'sm' => 'tw:btn-sm',
        'lg' => 'tw:btn-lg',
        default => '',
    };
@endphp

<{{ $tag }} {{ $attributes->merge([
    'class' => 'tw:btn ' . $typeClass . ($outline ? ' tw:btn-outline' : '') . ($sizeClass ? " $sizeClass" : '') . ' btn btn-' . $type
] + ($tag == 'a' ? ['href' => $link] : [])) }}>
    {{ $slot }}
</{{ $tag }}>
