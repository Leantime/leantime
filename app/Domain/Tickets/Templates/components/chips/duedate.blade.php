@props([
    'contentRole' => 'ghost',
    'variant' => '',
    'labelPosition' => 'top',
    'ticket' => null,
    'showLabel' => false
])

@php

    try{
      $dueDate = dtHelper()->parseDbDateTime($ticket->dateToFinish);
      $daysUntil = dtHelper()->userNow()->daysUntil($dueDate);

      if($daysUntil < 2) {
          $state = 'error';
      }elseif($daysUntil >= 2 && $daysUntil < 5){
        $state = 'warning';
      }else{
          $state = 'default';
      }

    }catch(Exception $e){
        $state = 'default';
    }
@endphp

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
    :content-role="$contentRole"
    :state="$state"
>
    <x-slot:leading-visual>
        <x-global::content.icon icon="alarm" class="text-lg text-trivial" />
    </x-slot:leading-visual>

    @if($showLabel)
        <x-slot:label-text>
            <x-global::content.icon icon="alarm"/> <span>{!!  __('label.due') !!}</span>
        </x-slot:label-text>
    @endif

</x-global::forms.datepicker>
