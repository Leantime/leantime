@props([
    'contentRole' => 'ghost',
    'variant' => 'default',
    'labelPosition' => 'left',
    'showLabel' => false,
    'sprints' => [],
    'labelPosition' => 'top',
    'ticket' => null,
])

<x-global::forms.select
    id=''
    name='sprintId'
    search="false"
    :dropdown-position="$dropdownPosition"
    :label-position="$labelPosition"
    :variant="$variant"
    :content-role="$contentRole"
    hx-post="{{ BASE_URL }}/hx/tickets/ticket/patch/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none"
    class="{{ $variant !== 'chip' ? 'select-bordered' : '' }}"
    >

    @if($showLabel)
        <x-slot:label-text>
            <x-global::content.icon icon="restart_alt" /> {{ __('label.sprint')  }}
        </x-slot:label-text>
   @endif

    <x-slot:validation-text>
    </x-slot:validation-text>

    <x-global::forms.select.option :value="''">
        @if($variant == 'chip')
            <x-global::elements.badge state="trivial" :outline="true">
                {{  __('label.not_assigned_to_sprint') }}
            </x-global::elements.badge>
        @else
            {{  __('label.not_assigned_to_sprint') }}
        @endif
    </x-global::forms.select.option>
    @foreach ($sprints as $sprintRow)
        <x-global::forms.select.option
            :value="strtolower($sprintRow->id)"
            :selected="strtolower($sprintRow->id ) == strtolower($ticket->sprint ?? '') ? 'true' : 'false'">

            @if($variant == 'chip')
                <x-global::elements.badge :state="$milestone->tags" content-role="primary">
                    {{  $sprintRow->name }}  @if(!empty($sprintRow->startDate) && dtHelper()->isValidDateString($sprintRow->startDate)) <small>({{ format($sprintRow->startDate)->date() }} - {{ format($sprintRow->endDate)->date() }})</small> @endif
                </x-global::elements.badge>
            @else
                {{ $sprintRow->name }} @if(!empty($sprintRow->startDate) && dtHelper()->isValidDateString($sprintRow->startDate)) <small>({{ format($sprintRow->startDate)->date() }} - {{ format($sprintRow->endDate)->date() }})</small> @endif
            @endif

        </x-global::forms.select.option>
    @endforeach

</x-global::forms.select>
