@props([
    'contentRole' => null,        // primary|secondary|ghost|accent|link|default
    'type' => null,               // v1 alias for contentRole
    'state' => 'default',         // default|info|warning|danger|success
    'scale' => null,              // xs|s|m|l|xl
    'size' => null,               // v1 alias for scale (sm|md|lg)
    'element' => null,            // a|button
    'tag' => null,                // v1 alias for element
    'leadingVisual' => null,      // icon class
    'trailingVisual' => null,     // icon class
    'icon' => null,               // v1 alias for leadingVisual
    'iconPosition' => 'pre',      // v1: pre|post
    'submit' => false,
    'disabled' => false,
    'loading' => false,
    'circle' => false,
    'formModal' => false,
    'href' => '#',
    'link' => null,               // v1 alias for href
    'outline' => false,
])

@php
    // Resolve v1 → v3 aliases
    $resolvedRole = $contentRole ?? $type ?? 'primary';
    $resolvedScale = $scale ?? match($size) {
        'sm' => 's', 'lg' => 'l', default => $size,
    } ?? 'm';
    $resolvedElement = $element ?? $tag ?? 'a';
    $resolvedHref = $link ?? $href;

    // Resolve icon aliases
    $resolvedLeading = $leadingVisual ?? ($iconPosition === 'pre' ? $icon : null);
    $resolvedTrailing = $trailingVisual ?? ($iconPosition === 'post' ? $icon : null);

    // Resolve element tag
    $resolvedTag = $submit ? 'button' : $resolvedElement;

    // Map contentRole + state to Bootstrap .btn-* classes
    $bsTypeClass = match(true) {
        $state === 'danger'  => 'btn btn-danger',
        $state === 'success' => 'btn btn-success',
        $state === 'warning' => 'btn btn-warning',
        $state === 'info'    => 'btn btn-info',
        default => match($resolvedRole) {
            'primary'   => 'btn btn-primary',
            'secondary', 'default' => 'btn btn-default',
            'ghost'     => 'btn btn-default',
            'accent'    => 'btn btn-primary',
            'link'      => 'btn btn-link',
            default     => 'btn btn-' . $resolvedRole,
        },
    };

    // Map scale to Bootstrap size class
    $sizeClass = match($resolvedScale) {
        'xs' => 'btn-xs',
        's'  => 'btn-sm',
        'l'  => 'btn-lg',
        'xl' => 'btn-lg',
        default => '',
    };

    $classes = $bsTypeClass
        . ($outline ? ' btn-outline' : '')
        . ($sizeClass ? " $sizeClass" : '')
        . ($circle ? ' btn-circle' : '')
        . ($formModal ? ' formModal' : '');

    $extraAttrs = [];
    if ($resolvedTag === 'a') {
        $extraAttrs['href'] = $resolvedHref;
    }
    if ($submit) {
        $extraAttrs['type'] = 'submit';
    }
    if ($disabled) {
        $extraAttrs['disabled'] = 'disabled';
    }
@endphp

<{{ $resolvedTag }} {{ $attributes->merge(array_merge(['class' => $classes], $extraAttrs)) }}>
    @if($loading)
        <span class="loading-spinner"></span>
    @endif
    @if($resolvedLeading)
        <i class="{{ $resolvedLeading }}"></i>
    @endif
    {{ $slot }}
    @if($resolvedTrailing)
        <i class="{{ $resolvedTrailing }}"></i>
    @endif
</{{ $resolvedTag }}>
