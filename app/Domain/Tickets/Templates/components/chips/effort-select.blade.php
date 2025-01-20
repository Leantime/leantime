@props([
    'contentRole' => 'ghost',
    'variant' => 'default',
    'labelPosition' => 'left',
    'showLabel' => false,
    'efforts' => [],
    'labelPosition' => 'top',
    'ticket' => null,
])

<x-global::forms.select
    id=''
    name='storypoints'
    search="false"
    :dropdown-position="$dropdownPosition"
    :label-position="$labelPosition"
    :variant="$variant"
    :content-role="$contentRole"
    hx-post="{{ BASE_URL }}/hx/tickets/ticket/patch/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none"
    >

    @if($showLabel)
        <x-slot:label-text>
            <x-global::content.icon icon="elevation" /> {{ __('label.effort')  }}
        </x-slot:label-text>
   @endif

    <x-slot:validation-text>
    </x-slot:validation-text>

    <x-global::forms.select.option :value="''">
        <x-global::elements.badge state="trivial" :outline="true">
            {{  __('label.effort_not_defined') }}
        </x-global::elements.badge>
    </x-global::forms.select.option>
    @foreach ($efforts as $effortKey => $effortValue)
        <x-global::forms.select.option
            :value="strtolower($effortKey)"
            :selected="strtolower($effortKey) == strtolower($ticket->storypoints ?? '') ? 'true' : 'false'">

            <x-global::elements.badge :outline="true">
                {{  $effortValue }}
            </x-global::elements.badge>
        </x-global::forms.select.option>
    @endforeach

</x-global::forms.select>
