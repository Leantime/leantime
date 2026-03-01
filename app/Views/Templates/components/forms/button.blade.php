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
    'variant' => 'default',          // default|icon-only|circle
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

    // Resolve variant — circle bool is a v1 alias
    $resolvedVariant = match(true) {
        $variant === 'icon-only' => 'icon-only',
        $variant === 'circle'    => 'circle',
        $circle                  => 'circle',
        default                  => 'default',
    };

    // Map scale to Bootstrap size class
    $sizeClass = match($resolvedScale) {
        'xs' => 'btn-xs',
        's'  => 'btn-sm',
        'l'  => 'btn-lg',
        'xl' => 'btn-lg',
        default => '',
    };

    if ($resolvedVariant === 'icon-only') {
        // Icon-only: transparent, borderless — no standard .btn classes
        $classes = 'btn-icon-only'
            . ($sizeClass ? " $sizeClass" : '')
            . ($formModal ? ' formModal' : '');
    } elseif ($resolvedVariant === 'circle') {
        // Circle: round icon button with border
        $classes = 'btn btn-default btn-circle'
            . ($sizeClass ? " $sizeClass" : '')
            . ($formModal ? ' formModal' : '');
    } else {
        // Default: standard Bootstrap button
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

        $classes = $bsTypeClass
            . ($outline ? ' btn-outline' : '')
            . ($sizeClass ? " $sizeClass" : '')
            . ($formModal ? ' formModal' : '');
    }

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

    $hxVals = $attributes->get('hx-vals');
@endphp

<{{ $resolvedTag }} @if($hxVals) hx-vals='{!! $hxVals !!}' @endif {{ $attributes->except('hx-vals')->merge(array_merge(['class' => $classes], $extraAttrs)) }}>
    @if($loading)
        <span class="loading-spinner"></span>
    @endif
    @if($resolvedLeading)
        @if(str_contains($resolvedLeading, 'fa-') || str_starts_with($resolvedLeading, 'fa '))
            <i class="{{ $resolvedLeading }}"></i>
        @else
            <x-global::elements.icon :name="$resolvedLeading" />
        @endif
    @endif
    {{ $slot }}
    @if($resolvedTrailing)
        @if(str_contains($resolvedTrailing, 'fa-') || str_starts_with($resolvedTrailing, 'fa '))
            <i class="{{ $resolvedTrailing }}"></i>
        @else
            <x-global::elements.icon :name="$resolvedTrailing" />
        @endif
    @endif
</{{ $resolvedTag }}>
