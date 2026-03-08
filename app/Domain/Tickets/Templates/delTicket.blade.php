@php
    $ticket = $tpl->get('ticket');
    $error = $tpl->get('error');
@endphp

<x-globals::elements.section-title icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>

@if(!empty($error))
    {!! $error !!}
@elseif(is_object($ticket))
    <x-globals::actions.confirm-delete action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
        <p class="tw:mb-4">{{ __('text.confirm_ticket_deletion') }}</p>
    </x-globals::actions.confirm-delete>
@else
    <p>Ticket not found</p>
@endif
