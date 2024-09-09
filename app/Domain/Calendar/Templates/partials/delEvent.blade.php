@extends($layout)

@section('content')

<?php
$id = (int) $_GET['id'];
?>

<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<x-global::content.modal.form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delEvent/{{ $id }}">
    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
    <p>{{ __("text.confirm_event_deletion") }}</p><br />
    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
    <button type="submit"  class="btn btn-primary" id="saveAndClose" value="closeModal"><?=$tpl->__("buttons.yes_delete") ?></button>
    <a class="btn btn-primary" href="{{ BASE_URL }}/calendar/showMyCalendar">{{ __("buttons.back") }}</a>
    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
</x-global::content.modal.form>

