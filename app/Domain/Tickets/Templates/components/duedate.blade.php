@props([
    'contentRole' => 'ghost',
    'variant' => '',
    'labelPosition' => 'top',
    'date' => '',
])

<x-global::forms.datepicker
    no-date-label="{{ __('text.anytime') }}"
    :value="$date"
    name="test1"
    :label-position="$labelPosition"
    :variant="$variant"
>
    <x-slot:leading-visual>
    </x-slot:leading-visual>

    <x-slot:label-text>
        <x-global::content.icon icon="acute" /> Due Date
    </x-slot:label-text>

</x-global::forms.datepicker>
