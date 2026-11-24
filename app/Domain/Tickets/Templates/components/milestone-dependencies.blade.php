@props([
    'dependencies' => [],   // ids of the milestones this milestone depends on
    'milestones' => [],     // milestone objects (->id, ->headline, ->tags = color)
])

{{-- The milestones a milestone depends on, colored with their milestone colors. Read only: edit them in the milestone dialog. --}}
@php
    $noMilestoneColor = '#b0b0b0';
    $milestonesById = [];
    foreach ($milestones as $milestone) {
        $milestonesById[(int) $milestone->id] = $milestone;
    }
@endphp
@forelse ($dependencies as $dependencyId)
    @php $dependency = $milestonesById[(int) $dependencyId] ?? null; @endphp
    @continue($dependency === null)
    <span {{ $attributes->class(['label', 'label-default', 'milestone']) }} style="background-color:{{ ($dependency->tags ?? '') !== '' ? $dependency->tags : $noMilestoneColor }}">{{ $dependency->headline }}</span>
@empty
    <span {{ $attributes->class(['label', 'label-default', 'milestone']) }} style="background-color:{{ $noMilestoneColor }}">{{ __('label.no_dependency') }}</span>
@endforelse
