@props([
    'contentRole' => 'ghost',
    'variant' => '',
    'labelPosition' => 'top',
    'ticket' => null,
])

<x-global::forms.datepicker
    no-date-label="{{ __('text.anytime') }}"
    :value="$ticket->dateToFinish"
    name="dateToFinish"
    dateName="dueDate-{{ $ticket->id }}"
    :label-position="$labelPosition"
    :variant="$variant"
    hx-post="{{ BASE_URL }}/hx/tickets/ticket/patch/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none"
>
    <x-slot:leading-visual>
        <x-global::content.icon icon="acute" class="text-lg h-5 w-5 leading-5" />
    </x-slot:leading-visual>

    <x-slot:label-text></x-slot:label-text>

</x-global::forms.datepicker>
