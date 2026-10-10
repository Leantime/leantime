@props([
    'ticketId',
    'sprintId' => null,   // current sprint id (0/''/-1 = none)
    'sprintName' => '',
    'sprints' => [],      // sprint objects (->id, ->name)
    'float' => true,
    'align' => 'start',
])

{{-- A to-do's sprint chip. Saves `sprint`. --}}
@php
    $hasSprint = $sprintId !== null && $sprintId !== '' && (string) $sprintId !== '0' && (string) $sprintId !== '-1';
@endphp
<x-global::forms.chip
    {{ $attributes }}
    type="sprint"
    field="sprint"
    :entity-id="$ticketId"
    :value="$hasSprint ? $sprintId : 0"
    :label="$hasSprint ? $sprintName : __('label.not_assigned_to_sprint')"
    :toggle-class="$float ? 'label-default sprint f-left' : 'label-default sprint'"
    :header="__('dropdown.choose_sprint')"
    :align="$align">
    <x-global::forms.chip.option value="0" :label="__('label.not_assigned_to_sprint')">{{ __('label.not_assigned_to_sprint') }}</x-global::forms.chip.option>
    @foreach ($sprints ?: [] as $sprint)
        <x-global::forms.chip.option :value="$sprint->id" :label="$sprint->name" id="ticketSprintChange{{ $ticketId }}{{ $sprint->id }}">{{ $sprint->name }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
