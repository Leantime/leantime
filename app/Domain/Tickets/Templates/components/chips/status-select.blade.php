@props([
    'contentRole' => 'primary',
    'variant' => 'chip', //chip, select
    'statuses' => [],
    'label' => true,
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

    @if($label)
        <x-slot:label-text>
            <x-global::content.icon icon="emergency_heat" /> {!!  __('label.priority') !!}
        </x-slot:label-text>
    @endif



    @foreach ($statuses as $key => $label)
        <x-global::forms.select.option
            :value="strtolower($key)"
            :selected="strtolower($key) == strtolower( $ticket->status ?? '') ? 'true' : 'false'">

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
