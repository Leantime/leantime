<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>

@if (!empty($error))
    {!! $error !!}
@else

    @if (is_object($ticket))
        <form method="post" action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
            <p>{!! __('text.confirm_ticket_deletion') !!}</p><br />
            <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />

            <a class="btn btn-primary" href="#/tickets/showTicket/{{ $ticket->id }}">{!! __('buttons.back') !!}</a>


        </form>

    @else
        <p>Ticket not found</p>
    @endif
@endif
