{{-- Backward-compat wrapper: maps old API → actions.chip naming-doc API --}}
@props([
    'type' => '',
    'selectedClass' => '',
    'selectedKey' => '',
    'parentId' => '',
    'options' => [],
    'extraClass' => '',
    'linkStyle' => '',
    'submit' => "false",
    'colorized' => false,
    'noBg' => false,
    'align' => 'end',
    'headerLabel' => '',
    'hxPost' => '',
    'hxSwap' => 'none',
    'hxIndicator' => '',
])

<x-globals::actions.chip
    :content-role="$type"
    :selected-class="$selectedClass"
    :selected-key="$selectedKey"
    :parent-id="$parentId"
    :options="$options"
    :extra-class="$extraClass"
    :link-style="$linkStyle"
    :submit="$submit"
    :colorized="$colorized"
    :no-bg="$noBg"
    :align="$align"
    :header-label="$headerLabel"
    :hx-post="$hxPost"
    :hx-swap="$hxSwap"
    :hx-indicator="$hxIndicator"
    {{ $attributes }}
/>
