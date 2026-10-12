{{--
    elements.status-dot — an 8px dot in a tone colour, for a status shown next to text ("● On track").

    Give it a label: either text next to it, or a `label` here, which becomes the screen-reader text.

    Props:
      tone   string  'default' | 'accent' | 'success' | 'warning' | 'danger' | 'info' | 'muted'.
      label  string  Optional accessible name when the dot stands alone (also its tooltip).

    Anything else passes through to the dot.
--}}
@props([
    'tone' => 'default',
    'label' => null,
])

@php
    $tones = ['default', 'accent', 'success', 'warning', 'danger', 'info', 'muted'];
    $tone = in_array($tone, $tones, true) ? $tone : 'default';
@endphp

<span {{ $attributes->class(['lt-status-dot', 'lt-badge--' . $tone]) }}
    @if ($label) role="img" aria-label="{{ $label }}" title="{{ $label }}" @else aria-hidden="true" @endif></span>
