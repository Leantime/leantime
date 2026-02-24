{{-- Backward-compat wrapper: maps old API → forms.button naming-doc API --}}
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
    // Map old type → contentRole + state
    $contentRole = match($type) {
        'danger', 'error' => 'secondary',
        'transparent'     => 'link',
        'ghost'           => 'ghost',
        'accent'          => 'accent',
        'success'         => 'secondary',
        'warning'         => 'secondary',
        default           => $type,
    };

    $state = match($type) {
        'danger', 'error' => 'danger',
        'success'         => 'success',
        'warning'         => 'warning',
        default           => 'default',
    };

    // Map old size → scale
    $scale = match($size) {
        'xs' => 'xs',
        'sm' => 's',
        'lg' => 'l',
        default => 'm',
    };

    // Map icon + iconPosition → leadingVisual / trailingVisual
    $leadingVisual = ($icon && $iconPosition === 'left') ? $icon : null;
    $trailingVisual = ($icon && $iconPosition === 'right') ? $icon : null;
@endphp

<x-globals::forms.button
    :content-role="$contentRole"
    :state="$state"
    :scale="$scale"
    :element="$tag"
    :leading-visual="$leadingVisual"
    :trailing-visual="$trailingVisual"
    :submit="$submit"
    :disabled="$disabled"
    :loading="$loading"
    :circle="$circle"
    :form-modal="$formModal"
    :href="$link"
    :outline="$outline"
    {{ $attributes }}
>{{ $slot }}</x-globals::forms.button>
