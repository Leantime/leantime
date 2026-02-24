@php
    $ticket = $tpl->get('ticket');
    $error = $tpl->get('error');
@endphp

<h4 class="widgettitle title-light">{{ __('subtitles.delete') }}</h4>

@if(!empty($error))
    {!! $error !!}
@elseif(is_object($ticket))
    <x-globals::actions.confirm-delete action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
        <p style="margin-bottom: 15px;">{{ __('text.confirm_ticket_deletion') }}</p>
    </x-globals::actions.confirm-delete>
@else
    <p>Ticket not found</p>
@endif
