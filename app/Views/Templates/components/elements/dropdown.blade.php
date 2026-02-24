{{-- Backward-compat wrapper: maps old API → actions.dropdown-menu --}}
@props([
    'label' => null,
    'icon' => null,
    'align' => 'end',
    'buttonClass' => '',
    'menuClass' => '',
    'containerClass' => '',
])

<x-globals::actions.dropdown-menu
    :label="$label"
    :leading-visual="$icon"
    variant="icon"
    :align="$align"
    :trigger-class="$buttonClass"
    :menu-class="$menuClass"
    :container-class="$containerClass"
    {{ $attributes }}
>{{ $slot }}</x-globals::actions.dropdown-menu>
