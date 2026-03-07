@php
    $ticket = $tpl->get('ticket');
@endphp

<h4 class="widgettitle title-light"><x-global::elements.icon name="delete" /> {{ __('label.delete_milestone') }}</h4>

<x-globals::actions.confirm-delete
    action="{{ BASE_URL }}/tickets/delMilestone/{{ $ticket->id }}"
    :message="__('text.confirm_milestone_deletion')"
    :buttonLabel="__('buttons.yes_delete')"
/>
