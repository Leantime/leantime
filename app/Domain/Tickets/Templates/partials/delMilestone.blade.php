<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get("ticket");
?>

<h4 class="widgettitle title-light">{{ __("subtitles.delete_milestone") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/tickets/delMilestone/{{ $ticket->id }}">
    <p>{{ __("text.confirm_milestone_deletion") }}</p><br />
    <input type="submit" value="{{ __("buttons.yes_delete") }}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL }}/tickets/roadmap/">{{ __("buttons.back") }}</a>
</x-global::content.modal.form>

