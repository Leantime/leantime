@props([
    'contentRole' => 'primary',
    'variant' => 'chip', //chip, select
    'statuses' => [],
    'showLabel' => false,
    'labelPosition' => 'top',
    'dropdownPosition' => 'left',
    'ticket' => null,
])

<x-global::forms.select
    name='status'
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
            <x-global::content.icon icon="clock_loader_90" /> {!!  __('label.status') !!}
        </x-slot:label-text>
    @endif



    @foreach ($statuses as $key => $label)
        <x-global::forms.select.option
            :value="strtolower($key)"
            :selected="strtolower($key) == strtolower( $ticket->status ?? '')">

            <x-global::elements.badge :state="$label['class']" :outline="true">

                @php
                    switch($label['statusType']){
                        case 'NEW':
                            $icon = 'circle';
                            break;
                        case 'INPROGRESS':
                            $icon = 'clock_loader_40';
                            break;
                        case 'DONE':
                            $icon = 'check_circle';
                            break;
                        default:
                            $icon = 'circle';
                    }
                @endphp
                <x-global::content.icon icon="{{ $icon }}" /> {{ $label['name'] }}
            </x-global::elements.badge>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
