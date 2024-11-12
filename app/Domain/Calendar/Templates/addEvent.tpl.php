<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('values');
?>


<?php echo $tpl->displayNotification() ?>

<h4 class="widgettitle title-light"><?php echo $tpl->__('subtitles.event'); ?></h4>

<form action="<?= BASE_URL?>/calendar/addEvent/" method="post" class='formModal'>

    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

    <label for="description"><?php echo $tpl->__('label.title') ?></label>
    <input type="text" id="description" name="description" value="<?php $tpl->e($values['description']); ?>" /><br />

    <div class="par">
        <label for="dateFrom"><?php echo $tpl->__('label.start_date') ?></label>
        <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for=""><?php echo $tpl->__('label.start_time') ?></label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="" />
           </div>
    </div>
    <div class="par">
        <label for="dateTo"><?php echo $tpl->__('label.end_date') ?></label>
        <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for=""><?php echo $tpl->__('label.end_time') ?> </label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="" />
           </div>
    </div>

    <label for="allDay"><?php echo $tpl->__('label.all_day') ?></label>
    <input type="checkbox" id="allDay" name="allDay"
    <?php if (isset($values['allDay']) === true && $values['allDay'] === true) {
        echo 'checked="checked" ';
    }?>
    /><br /><br />

    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

    <p class="stdformbutton">
        <input type="hidden" value="1" name="save" />
        <input type="submit" name="saveEvent" id="saveEvent" value="<?php echo $tpl->__('buttons.save') ?>" class="button" />
    </p>

    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

</form>

<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
