<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('values');
?>

<?php echo $tpl->displayNotification() ?>

<h4 class="widgettitle title-light"><?php echo $tpl->__('subtitles.event'); ?></h4>

<form action="<?=BASE_URL?>/calendar/editEvent/<?=$values['id'] ?>" method="post" class="formModal">

    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

    <label for="description"><?php echo $tpl->__('label.title') ?></label>
    <input type="text" id="description" name="description" value="<?php $tpl->e($values['description']); ?>" /><br />

    <label for="dateFrom"><?php echo $tpl->__('label.start_date') ?></label>
    <input type="text" id="event_date_from" autocomplete="off" name="dateFrom" value="<?php echo format($values['dateFrom'])->date(); ?>" />

    <div class="par">
        <label> <?php echo $tpl->__('label.start_time') ?></label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="<?php echo format($values['dateFrom'])->time24(); ?>" />
           </div>
    </div>

    <label for="dateTo"><?php echo $tpl->__('label.end_date') ?></label>
    <input type="text" id="event_date_to" autocomplete="off" name="dateTo" value="<?php echo format($values['dateTo'])->date(); ?>" />

    <div class="par">
        <label for=""><?php echo $tpl->__('label.end_time') ?> </label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="<?php echo format($values['dateTo'])->time24(); ?>" />
           </div>
    </div>

    <label for="allDay"><?php echo $tpl->__('label.all_day') ?></label>
    <input type="checkbox" id="allDay" name="allDay"
    <?php if ($values['allDay'] === 'true') {
        echo 'checked="checked" ';
    }?>
    />

    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

    <div class="clear"></div>
    <br />
    <a href="<?=BASE_URL?>/calendar/delEvent/<?=(int)$_GET['id'] ?>" class="formModal delete right"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete')?></a>
    <input type="hidden" value="1" name="save" />
    <input type="submit" name="saveEvent" id="save" value="<?php echo $tpl->__('buttons.save') ?>" class="button" />

    <div class="clear"></div>

    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

</form>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });
</script>
