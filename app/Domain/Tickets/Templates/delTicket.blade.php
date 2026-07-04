<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>

@if (!empty($error))
    {!! $error !!}
@else

    @if (is_object($ticket))
        <form method="post" action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
            <p>{!! __('text.confirm_ticket_deletion') !!}</p><br />
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />

            <x-global::forms.button tag="a" contentRole="tertiary" link="#/tickets/showTicket/{{ $ticket->id }}">{!! __('buttons.back') !!}</x-global::forms.button>


        </form>

    @else
        <p>Ticket not found</p>
    @endif
@endif
