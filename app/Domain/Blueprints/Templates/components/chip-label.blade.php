@props([
    'type',                // status | relates
    'itemId',
    'value' => null,       // current label key
    'labels' => [],        // key => ['title' => …, 'dropdown' => info|warning|success|danger…]
    'adapter' => 'canvas', // canvas (Blueprints boards) | goal (Goalcanvas)
    'canvasType' => null,  // canvas adapter: the board type, e.g. "leancanvas"
])

{{--
    A canvas item's status / relates chip (Blueprints boards and goals), colored label-{dropdown}.
    Saves the `status` / `relates` column through the canvas or goal adapter (core/chips.js).
--}}
@php
    $current = ($value !== null && $value !== '') ? ($labels[$value] ?? null) : null;
    $colorClass = fn ($label) => ! empty($label['dropdown']) ? 'label-'.$label['dropdown'] : '';
    $optionId = $type === 'status' ? 'ticketStatusChange' : 'ticketRelatesChange';
@endphp
<x-global::forms.chip
    {{ $attributes->class(['colorized', 'firstDropdown']) }}
    :type="$type"
    :field="$type"
    :adapter="$adapter"
    :canvas-type="$canvasType"
    :entity-id="$itemId"
    :value="$value"
    :label="$current['title'] ?? ''"
    :color-class="$current ? $colorClass($current) : ''"
    :toggle-class="'f-left '.$type"
    :header="__($type === 'status' ? 'dropdown.choose_status' : 'dropdown.choose_relates')">
    @foreach ($labels as $key => $label)
        <x-global::forms.chip.option :value="$key" :label="$label['title']" :color-class="$colorClass($label)" id="{{ $optionId }}{{ $itemId }}{{ $key }}">{{ $label['title'] }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
