<x-global::actions.modal-header>
    <x-slot:title>{!! __('subtitles.delete') !!}</x-slot:title>
</x-global::actions.modal-header>@if (!empty($error))
    {!! $error !!}
@else

    @if (is_object($ticket))
        <form method="post" action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
            <p>{!! __('text.confirm_ticket_deletion') !!}</p><br />
            @if (($subtaskCount ?? 0) > 0)
                <x-global::forms.checkbox name="deleteSubtasks" value="1">{{ sprintf(__('text.delete_subtasks_too'), $subtaskCount) }}</x-global::forms.checkbox>
                <br />
            @endif
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />

            <x-global::forms.button tag="a" contentRole="tertiary" link="#/tickets/showTicket/{{ $ticket->id }}">{!! __('buttons.back') !!}</x-global::forms.button>


        </form>

    @else
        <p>{{ __('label.ticketNotFound', 'Ticket not found') }}</p>
    @endif
@endif
