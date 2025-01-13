@props([
    'contentRole' => 'primary',
    'variant' => 'chip', //chip, select
    'priorities' => [],
    'label' => true,
    'labelPosition' => 'top',
    'dropdownPosition' => 'left',
    'ticket' => null,
])

<x-global::forms.select
    name='priority'
    search="false"
    :dropdown-position="$dropdownPosition"
    :label-position="$labelPosition"
    :variant="$variant"
    :content-role="$contentRole"
    hx-post="{{ BASE_URL }}/hx/tickets/ticket/patch/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none"
    >

    @if($label)
        <x-slot:label-text>
            <x-global::content.icon icon="emergency_heat" /> {!!  __('label.priority') !!}
        </x-slot:label-text>
    @endif

    <x-global::forms.select.option :value="''">
        <x-global::elements.badge state="trivial" :outline="true">
            {!!  __('label.priority_not_defined') !!}
        </x-global::elements.badge>
    </x-global::forms.select.option>

    @foreach ($priorities as $priorityKey => $priorityValue)
        <x-global::forms.select.option
            :value="strtolower($priorityKey)"
            :selected="strtolower($priorityKey) == strtolower( $ticket->priority ?? '') ? true : false">

            <x-global::elements.badge :state="$priorityKey" :outline="true">
                <x-global::content.icon icon="local_fire_department" fill="true"/> {!! $priorityValue !!}
            </x-global::elements.badge>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
