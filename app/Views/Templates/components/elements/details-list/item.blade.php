{{--
    elements.details-list.item — one row of an elements.details-list.

    Props:
      label  string  The fact's name (escaped).

    Slot: the value; markup allowed (a link, a badge, an avatar). Escape user text at the call site.
--}}
@props([
    'label',
])

<div {{ $attributes->class(['lt-details-row']) }}>
    <dt>{{ $label }}</dt>
    <dd>{{ $slot }}</dd>
</div>
