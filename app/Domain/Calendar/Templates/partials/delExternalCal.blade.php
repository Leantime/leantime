<x-global::content.modal.modal-buttons/>

<?php
$id = (int) $_GET['id'];
?>

<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/calendar/delExternalCalendar/{{ $id }}">
    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
    <p>{{ __("text.confirm_calendar_deletion") }}</p><br />
    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
    <button type="submit"  class="btn btn-primary" id="saveAndClose" value="closeModal"><?=$tpl->__("buttons.yes_delete") ?></button>
    <a class="btn btn-primary" href="{{ BASE_URL }}/calendar/showMyCalendar">{{ __("buttons.back") }}</a>
    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
</x-global::content.modal.form>

