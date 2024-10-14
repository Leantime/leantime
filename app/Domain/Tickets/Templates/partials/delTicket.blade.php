<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get("ticket");
?>


<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<?php if (is_object($ticket)) { ?>
<x-global::content.modal.form action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
    <p>{{ __("text.confirm_ticket_deletion") }}</p><br />
    <x-global::forms.button type="submit" name="del" class="button">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="#/tickets/showTicket/{{ $ticket->id }}">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    


</x-global::content.modal.form>

<?php } else { ?>
    <p>Ticket not found</p>
<?php } ?>
