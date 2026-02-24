{{-- Backward-compat wrapper: maps old API → actions.dropdown-menu --}}
@props([
    'label' => '',
    'type' => 'primary',
    'icon' => null,
    'align' => 'end',
    'menuClass' => '',
])

<x-globals::actions.dropdown-menu
    :label="$label"
    :leading-visual="$icon"
    variant="button"
    :content-role="$type"
    :align="$align"
    :menu-class="$menuClass"
    {{ $attributes }}
>{{ $slot }}</x-globals::actions.dropdown-menu>
