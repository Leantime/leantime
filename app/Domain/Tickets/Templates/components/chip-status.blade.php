@props([
    'ticketId',
    'status' => null,   // current status key
    'labels' => [],     // status key => ['name' => …, 'class' => 'label-…']
    'float' => true,    // the toggle floats left (most lists/cards); false inside table cells that center it
    'align' => 'start', // end -> menu opens right-aligned
    'iconOnly' => false, // just the colored caret (list view); the status name is kept for screen readers
])

{{-- A to-do's status chip. Saves `status` through the ticket adapter (core/chips.js). --}}
@php
    $current = $labels[$status] ?? null;
@endphp
<x-global::forms.chip
    {{ $attributes->class(['colorized']) }}
    type="status"
    field="status"
    :entity-id="$ticketId"
    :value="$status"
    :label="$current['name'] ?? __('label.status_unknown')"
    :color-class="$current['class'] ?? 'label-default'"
    :toggle-class="$float ? 'status f-left' : 'status'"
    :header="__('dropdown.choose_status')"
    :align="$align">
    @if ($iconOnly)
        <x-slot:toggle><span class="sr-only" data-chip-label>{{ $current['name'] ?? __('label.status_unknown') }}</span></x-slot:toggle>
    @endif
    @foreach ($labels as $key => $label)
        <x-global::forms.chip.option :value="$key" :label="$label['name']" :color-class="$label['class'] ?? ''" id="ticketStatusChange{{ $ticketId }}{{ $key }}">{{ $label['name'] }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
