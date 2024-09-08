<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get("ticket");
?>


<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<?php if (is_object($ticket)) { ?>
<x-global::content.modal.form action="{{ BASE_URL }}/tickets/delTicket/{{ $ticket->id }}">
    <p>{{ __("text.confirm_ticket_deletion") }}</p><br />
    <input type="submit" value="{{ __("buttons.yes_delete") }}" name="del" class="button" />

        <a class="btn btn-primary" href="#/tickets/showTicket/<?php echo $ticket->id ?>">{{ __("buttons.back") }}</a>


</x-global::content.modal.form>

<?php } else { ?>
    <p>Ticket not found</p>
<?php } ?>
