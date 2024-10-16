@props([
    'contentRole' => 'ghost',
    'variant' => 'default',
    'labelPosition' => 'left',
    '$ticket' => '',
    'efforts' => [],
    'labelPosition' => 'top'
])

<x-global::forms.select id='storypoints' name='storypoints' search="false" :variant="$variant" :label-position="$labelPosition"  :content-role="$contentRole">
    <x-slot:label-text>
        <x-global::content.icon icon="elevation" /> {{ __('label.effort')  }}
    </x-slot:label-text>
    <x-slot:validation-text>
    </x-slot:validation-text>

    <x-global::forms.select.option :value="''">
        {{  __('label.effort_not_defined') }}
    </x-global::forms.select.option>
    @foreach ($efforts as $effortKey => $effortValue)
        <x-global::forms.select.option
            :value="strtolower($effortKey)"
            :selected="strtolower($effortKey) == strtolower($ticket->storypoints ?? '') ? 'true' : 'false'">
            {{  $effortValue }}
        </x-global::forms.select.option>
    @endforeach

</x-global::forms.select>