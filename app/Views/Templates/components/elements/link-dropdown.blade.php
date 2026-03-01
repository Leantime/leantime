{{-- Backward-compat wrapper: maps old API → actions.dropdown-menu --}}
@props([
    'label' => '',
    'icon' => 'arrow_drop_down',
    'align' => 'end',
    'menuClass' => '',
    'triggerClass' => '',
])

<x-globals::actions.dropdown-menu
    :label="$label"
    :trailing-visual="$icon"
    variant="link"
    :align="$align"
    :menu-class="$menuClass"
    :trigger-class="$triggerClass"
    {{ $attributes }}
>{{ $slot }}</x-globals::actions.dropdown-menu>
