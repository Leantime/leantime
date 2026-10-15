{{--
    forms.button-group — buttons that belong together.

    Two looks:
      joined (default)  Buttons share borders, outer corners rounded ("Save | ▾").
      segmented         A pill track where one option is picked ("Tickets | Effort | Hours",
                        "Monthly | Yearly"). The picked button has `.active`; clicking another moves
                        `.active` and `aria-pressed` to it (core/snippets.js), so a page's own click
                        handler only has to react to the choice.

    Props:
      label      string  Accessible name of the group (aria-label), e.g. "Chart metric".
      segmented  bool    Segmented look and single-choice behaviour.

    Slot: the buttons (`forms.button`, real <button type="button">s, not links). Mark the starting
    choice with class="active" aria-pressed="true" and the others aria-pressed="false"; groups that
    render without aria-pressed get it from .active on load (core/snippets.js).
    Anything else (id, class, data-*) passes through to the group.
--}}
@props([
    'label' => null,
    'segmented' => false,
])

<div role="group" @if ($label) aria-label="{{ $label }}" @endif
    {{ $attributes->class(['lt-btn-group', 'lt-btn-group--segmented' => $segmented]) }}>
    {{ $slot }}
</div>
