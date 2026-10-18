{{--
    tickets::group-section — one group of a grouped to-do view (table, list, milestones): an
    elements.accordion titled with the group's label and count, coloured like the group (its
    milestone colour, else its status colour). The ungrouped "all" group renders its content as is.

    Props:
      group     array   An entry of Tickets::getAllGrouped(): label (rendered raw — the service
                        escapes it), id, class, color, more-info, items.
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
    // The status label classes the old group headers coloured their titles with.
    $statusColors = [
        'label-warning' => 'var(--yellow)',
        'label-important' => 'var(--red)',
        'label-info' => 'var(--dark-blue)',
        'label-danger' => 'var(--dark-red)',
        'label-success' => 'var(--green)',
        'label-default' => 'var(--grey)',
    ];
    $groupColor = ! empty($group['color']) ? $group['color'] : ($statusColors[$group['class'] ?? ''] ?? null);
@endphp

@if ($group['label'] === 'all')
    {{ $slot }}
@else
    <x-global::elements.accordion
        :id="'tickets-' . $groupBy . '-' . $group['id']"
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
