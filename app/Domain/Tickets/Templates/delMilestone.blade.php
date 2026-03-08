@php
    $ticket = $tpl->get('ticket');
@endphp

<x-globals::elements.section-title icon="delete">{{ __('label.delete_milestone') }}</x-globals::elements.section-title>

<x-globals::actions.confirm-delete
    action="{{ BASE_URL }}/tickets/delMilestone/{{ $ticket->id }}"
    :message="__('text.confirm_milestone_deletion')"
    :buttonLabel="__('buttons.yes_delete')"
/>
