@props([
    'link' => '#',
    'type' => 'primary',
    'tag'  => 'a',
    'size' => null,
    'outline' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'submit' => false,
    'disabled' => false,
    'loading' => false,
    'circle' => false,
    'formModal' => false,
])

@php
    // If submit shorthand is used, force tag to button with type=submit
    $resolvedTag = $submit ? 'button' : $tag;

    // Map to Bootstrap classes
    $bsTypeClass = match($type) {
        'danger'      => 'btn btn-danger',
        'error'       => 'btn btn-danger',
        'transparent' => 'btn btn-link',
        'ghost'       => 'btn btn-default',
        'accent'      => 'btn btn-primary',
        default       => 'btn btn-' . $type,
    };

    $sizeClass = match($size) {
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };

    $classes = $bsTypeClass
        . ($outline ? ' btn-outline' : '')
        . ($sizeClass ? " $sizeClass" : '')
        . ($circle ? ' btn-circle' : '')
        . ($formModal ? ' formModal' : '');

    $extraAttrs = [];
    if ($resolvedTag === 'a') {
        $extraAttrs['href'] = $link;
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
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }}"></i>
    @endif
</{{ $resolvedTag }}>
