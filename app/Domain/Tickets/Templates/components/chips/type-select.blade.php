@props([
    'contentRole' => 'default',
    'variant' => '',
    'ticketTypes' => '',
    'labelPosition' => 'top',
])

@php
    $typeIcons = [
        'story' => 'fa-book',
        'task' => 'fa-check-square',
        'subtask' => 'fa-diagram-successor',
        'bug' => 'fa-bug',
    ];
@endphp

<x-global::forms.select id='type' name='type' search="false" :content-role="$contentRole" :label-position="$labelPosition" :variant="$variant">
    @foreach ($ticketTypes as $type)
        <x-global::forms.select.option :value="strtolower($type)" :selected="strtolower($type) == strtolower($ticket->type ?? '') ? 'true' : 'false'">
            <span class="size-sm pr-xs"><x-global::content.icon :icon="strtolower($type)" />
                {!! __('label.' . strtolower($type)) !!}
        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
