@props([
    'ticketId',
    'storypoints' => null,  // current effort key
    'efforts' => [],        // effort key => label (XS, S, M…)
    'float' => true,
    'align' => 'start',
])

{{-- A to-do's effort (t-shirt size) chip. Saves `storypoints`. --}}
@php
    $hasEffort = $storypoints !== null && $storypoints !== '' && $storypoints > 0;
    $currentLabel = $hasEffort ? ($efforts[(string) $storypoints] ?? $storypoints) : __('label.story_points_unkown');
@endphp
<x-global::forms.chip
    {{ $attributes }}
    type="effort"
    field="storypoints"
    :entity-id="$ticketId"
    :value="$storypoints"
    :label="$currentLabel"
    :toggle-class="$float ? 'label-default effort f-left' : 'label-default effort'"
    :header="__('dropdown.how_big_todo')"
    :align="$align">
    @foreach ($efforts as $effortKey => $effortLabel)
        <x-global::forms.chip.option :value="$effortKey" :label="$effortLabel" id="ticketEffortChange{{ $ticketId }}{{ $effortKey }}">{{ $effortLabel }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
