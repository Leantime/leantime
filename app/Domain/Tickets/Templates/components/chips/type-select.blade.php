@props([
    'contentRole' => 'default',
    'variant' => '',
    'ticketTypes' => '',
    'labelPosition' => 'top',
    'ticket' => null
])

@php
    $typeIcons = [
        'story' => 'fa-book',
        'task' => 'fa-check-square',
        'subtask' => 'fa-diagram-successor',
        'bug' => 'fa-bug',
    ];
@endphp

<x-global::forms.select
    id='type'
    name='type'
    search="false"
    :content-role="$contentRole"
    :label-position="$labelPosition"
    :variant="$variant"
    hx-post="{{ BASE_URL }}/hx/tickets/ticket/patch/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none"
>
    @foreach ($ticketTypes as $type)
        <x-global::forms.select.option
            :value="strtolower($type)"
            :selected="strtolower($type) == strtolower($ticket->type ?? '') ? 'true' : 'false'">


            <x-global::elements.badge :outline="true">
                <x-global::content.icon :icon="strtolower($type)" />
                {!! __('label.' . strtolower($type)) !!}
            </x-global::elements.badge>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
