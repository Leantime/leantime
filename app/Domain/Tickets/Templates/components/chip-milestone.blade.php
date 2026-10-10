@props([
    'ticketId',
    'milestoneId' => null,  // current milestone id (0/'' = none)
    'headline' => '',       // current milestone headline
    'color' => '',          // current milestone color
    'milestones' => [],     // milestone objects (->id, ->headline, ->tags = color)
    'float' => true,
    'align' => 'start',
])

{{-- A to-do's milestone chip, colored with the milestone's color. Saves `milestoneid`. --}}
@php
    $noMilestoneColor = '#b0b0b0';
    $hasMilestone = $milestoneId !== null && $milestoneId !== '' && (string) $milestoneId !== '0';
@endphp
<x-global::forms.chip
    {{ $attributes->class(['colorized']) }}
    type="milestone"
    field="milestoneid"
    :entity-id="$ticketId"
    :value="$hasMilestone ? $milestoneId : 0"
    :label="$hasMilestone ? $headline : __('label.no_milestone')"
    :color="$hasMilestone && $color !== '' ? $color : $noMilestoneColor"
    :toggle-class="$float ? 'label-default milestone f-left' : 'label-default milestone'"
    :header="__('dropdown.choose_milestone')"
    :align="$align">
    <x-global::forms.chip.option value="0" :label="__('label.no_milestone')" :color="$noMilestoneColor">{{ __('label.no_milestone') }}</x-global::forms.chip.option>
    @foreach ($milestones as $milestone)
        <x-global::forms.chip.option :value="$milestone->id" :label="$milestone->headline" :color="$milestone->tags ?? ''" id="ticketMilestoneChange{{ $ticketId }}{{ $milestone->id }}">{{ $milestone->headline }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
