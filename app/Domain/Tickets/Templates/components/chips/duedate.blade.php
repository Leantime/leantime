@props([
    'contentRole' => 'ghost',
    'variant' => '',
    'labelPosition' => 'top',
    'ticket' => null,
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
        <x-global::content.icon icon="acute" class="text-lg" />
    </x-slot:leading-visual>

    <x-slot:label-text></x-slot:label-text>

</x-global::forms.datepicker>
