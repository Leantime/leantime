{{--
    elements.info-tip — a small (?) button that explains the thing next to it.

    Opens on hover, focus or tap (also on touch screens, where plain data-tippy-content tooltips are
    switched off), and the text is in the page for screen readers (aria-describedby), so the
    explanation never depends on hovering.

    Props:
      text   string  The explanation (escaped).
      label  string  Accessible name of the button; default "More information".

    Anything else (class, id) passes through to the button.
--}}
@props([
    'text',
    'label' => null,
])

@php
    $descriptionId = 'lt-info-' . bin2hex(random_bytes(4));
@endphp

<button type="button" aria-label="{{ $label ?? __('label.more_information') }}" aria-describedby="{{ $descriptionId }}" aria-expanded="false"
    data-lt-info="{{ $text }}" {{ $attributes->class(['lt-info-tip']) }}>
    <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
    {{-- Inside the button so a heading that holds the tip keeps its own name (the button's name is its label). --}}
    <span id="{{ $descriptionId }}" class="sr-only">{{ $text }}</span>
</button>
