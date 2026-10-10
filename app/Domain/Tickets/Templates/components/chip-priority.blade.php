@props([
    'ticketId',
    'priority' => null,   // current priority key (1 = critical … 5 = lowest)
    'priorities' => [],   // priority key => label
    'float' => true,
    'align' => 'start',
])

{{-- A to-do's priority chip, colored priority-bg-{key}. Saves `priority`. --}}
@php
    $hasPriority = $priority !== null && $priority !== '' && $priority > 0;
@endphp
<x-global::forms.chip
    {{ $attributes }}
    type="priority"
    field="priority"
    :entity-id="$ticketId"
    :value="$priority"
    :label="$hasPriority ? ($priorities[$priority] ?? __('label.priority_unkown')) : __('label.priority_unkown')"
    :color-class="$hasPriority ? 'priority-bg-'.$priority : ''"
    :toggle-class="$float ? 'label-default priority f-left' : 'label-default priority'"
    :header="__('dropdown.select_priority')"
    :align="$align">
    @foreach ($priorities as $priorityKey => $priorityLabel)
        <x-global::forms.chip.option :value="$priorityKey" :label="$priorityLabel" :color-class="'priority-bg-'.$priorityKey" id="ticketPriorityChange{{ $ticketId }}{{ $priorityKey }}">{{ $priorityLabel }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
