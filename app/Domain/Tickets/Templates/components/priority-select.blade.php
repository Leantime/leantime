@props([
    'contentRole' => 'ghost',
    'variant' => '',
    'priorities' => [],
    'priorityValue' => '',
    'labelPosition' => 'top'
])

<x-global::forms.select id='priority2' name='priority2' search="false" :label-position="$labelPosition" :variant="$variant" :content-role="$contentRole">

    <x-slot:label-text>
        <x-global::content.icon icon="emergency_heat" /> {!!  __('label.priority') !!}
    </x-slot:label-text>

    <x-global::forms.select.option :value="''">
        {{  __('label.priority_not_defined') }}
    </x-global::forms.select.option>

    @foreach ($priorities as $priorityKey => $priorityValue)
        <x-global::forms.select.option
            :value="strtolower($priorityKey)"
            :selected="strtolower($priorityKey) == strtolower( $ticket->priority ?? '') ? 'true' : 'false'">

            <span class="priority-text-{{ $priorityKey }} ">
                <x-global::content.icon icon="local_fire_department" /> {{ $priorityValue }}
            </span>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
