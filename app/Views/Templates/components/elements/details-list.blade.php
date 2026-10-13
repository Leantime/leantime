{{--
    elements.details-list — read-only facts about one thing: "Owner  Marcel", "Due  Oct 30".

    Labels 12 / semibold uppercase muted, values 14 regular (type rule in COMPONENTS.md).

    Props:
      items   array   Optional label => value pairs (values escaped). For values with markup
                      (links, badges, avatars) use <x-global::elements.details-list.item> rows in the slot.
      layout  string  'stacked' (label above value, default; fits side panels) |
                      'inline' (label column + value column; fits wide cards).

    Slot: <x-global::elements.details-list.item label="…">value</x-global::elements.details-list.item> rows.
    Anything else passes through to the <dl>.
--}}
@props([
    'items' => [],
    'layout' => 'stacked',
])

<dl {{ $attributes->class(['lt-details', 'lt-details--inline' => $layout === 'inline']) }}>
    @foreach ($items as $itemLabel => $itemValue)
        <div class="lt-details-row">
            <dt>{{ $itemLabel }}</dt>
            <dd>{{ $itemValue }}</dd>
        </div>
    @endforeach
    {{ $slot }}
</dl>
