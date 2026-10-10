@props([
    'ideaId',
    'box' => null,   // current idea board column key
    'labels' => [],  // key => ['name' => …, 'class' => 'label-…']
])

{{-- An idea's status (board column) chip. Saves the idea's `box` through the idea adapter. --}}
@php
    $current = $labels[$box] ?? null;
@endphp
<x-global::forms.chip
    {{ $attributes->class(['firstDropdown', 'colorized']) }}
    type="status"
    field="box"
    adapter="idea"
    :entity-id="$ideaId"
    :value="$box"
    :label="$current['name'] ?? ''"
    :color-class="$current['class'] ?? ''"
    toggle-class="f-left status"
    :header="__('dropdown.choose_status')">
    @foreach ($labels as $key => $label)
        <x-global::forms.chip.option :value="$key" :label="$label['name']" :color-class="$label['class'] ?? ''" id="ticketStatusChange{{ $ideaId }}{{ $key }}">{{ $label['name'] }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
