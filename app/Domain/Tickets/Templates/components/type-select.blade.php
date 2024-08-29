@php
    $typeIcons = array('story' => 'fa-book', 'task' => 'fa-check-square', 'subtask' => 'fa-diagram-successor', 'bug' => 'fa-bug');
@endphp

<x-global::forms.select id='type' name='type' search="false">
    @foreach ($ticketTypes as $type)
        <x-global::forms.select.option
            :value="strtolower($type)"
            :selected="strtolower($type) == strtolower($ticket->type ?? '') ? 'true' : 'false'">
            <span class="tw-size-sm tw-pr-xs"><i class="fa {{ $typeIcons[strtolower($type)] }}"></i></span> {{  __("label." . strtolower($type)) }}
        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
