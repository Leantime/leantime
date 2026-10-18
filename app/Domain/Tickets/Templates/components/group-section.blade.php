{{--
    tickets::group-section — one group of a grouped to-do view (table, list, milestones): an
    elements.accordion titled with the group's label and count, coloured like the group (its
    milestone colour, else its status or priority colour). The ungrouped "all" group renders its content as is.

    Props:
      group     array   An entry of Tickets::getAllGrouped(): label (rendered raw — the service
                        escapes it), id, value, class, color, more-info, items.
      groupBy   string  The active group-by field, so a collapsed group is remembered per field
                        (status 3 and milestone 3 are different groups).
      subtitle  string  Optional line under the title, e.g. the group's more-info.
--}}
@props([
    'group',
    'groupBy' => '',
    'subtitle' => null,
])

@php
    // The group classes the old headers coloured their titles with: status labels and priorities.
    $classColors = [
        'label-warning' => 'var(--yellow)',
        'label-important' => 'var(--red)',
        'label-info' => 'var(--dark-blue)',
        'label-danger' => 'var(--dark-red)',
        'label-success' => 'var(--green)',
        'label-default' => 'var(--grey)',
        'priority-text-1' => 'var(--dark-red)',
        'priority-text-2' => 'var(--red)',
        'priority-text-3' => 'var(--yellow)',
        'priority-text-4' => 'var(--green)',
        'priority-text-5' => 'var(--dark-grey)',
    ];
    $groupColor = ! empty($group['color']) ? $group['color'] : ($classColors[$group['class'] ?? ''] ?? null);
    // Remember the state by the grouped value (milestone id, user id, status …): `id` is a sort key
    // built from the name for milestone and parent-task groups, so two similar names would collide.
    $stateKey = (string) ($group['value'] ?? '') !== '' ? $group['value'] : $group['id'];
@endphp

@if ($group['label'] === 'all')
    {{ $slot }}
@else
    <x-global::elements.accordion
        :id="'tickets-' . $groupBy . '-' . $stateKey"
        open
        :color="$groupColor"
        :count="count($group['items'])"
        :subtitle="$subtitle"
        {{ $attributes }}
    >
        <x-slot:title>{!! $group['label'] !!}</x-slot:title>
        {{ $slot }}
    </x-global::elements.accordion>
@endif
