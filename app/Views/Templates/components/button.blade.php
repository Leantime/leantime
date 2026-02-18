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

    $typeClass = match($type) {
        'primary'     => 'tw:btn-primary',
        'secondary'   => 'tw:btn-secondary',
        'accent'      => 'tw:btn-accent',
        'info'        => 'tw:btn-info',
        'success'     => 'tw:btn-success',
        'warning'     => 'tw:btn-warning',
        'error'       => 'tw:btn-error',
        'danger'      => 'tw:btn-error',
        'ghost'       => 'tw:btn-ghost',
        'link'        => 'tw:btn-link',
        'transparent' => 'tw:btn-ghost',
        default       => 'tw:btn-primary',
    };

    // Map to Bootstrap classes
    $bsTypeClass = match($type) {
        'danger'      => 'btn btn-danger',
        'transparent' => 'btn btn-link',
        default       => 'btn btn-' . $type,
    };

    $sizeClass = match($size) {
        'xs' => 'tw:btn-xs',
        'sm' => 'tw:btn-sm',
        'lg' => 'tw:btn-lg',
        default => '',
    };

    $classes = 'tw:btn ' . $typeClass
        . ($outline ? ' tw:btn-outline btn-outline' : '')
        . ($sizeClass ? " $sizeClass" : '')
        . ($circle ? ' tw:btn-circle btn-circle' : '')
        . ($loading ? ' tw:loading tw:loading-spinner' : '')
        . ($formModal ? ' formModal' : '')
        . ' ' . $bsTypeClass;

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
        <span class="tw:loading tw:loading-spinner tw:loading-xs"></span>
    @endif
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }}"></i>
    @endif
</{{ $resolvedTag }}>
