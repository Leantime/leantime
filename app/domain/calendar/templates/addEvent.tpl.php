<?php
    defined('RESTRICTED') or die('Restricted access');
    $values = $this->get('values');
?>


<?php echo $this->displayNotification() ?>

<h4 class="widgettitle title-light"><?php echo $this->__('subtitles.event'); ?></h4>

<form action="<?=BASE_URL?>/calendar/addEvent/" method="post" class='formModal'>

    <?php $this->dispatchTplEvent('afterFormOpen'); ?>

    <label for="description"><?php echo $this->__('label.title') ?></label>
    <input type="text" id="description" name="description" value="<?php $this->e($values['description']); ?>" /><br />

    <div class="par">
        <label for="dateFrom"><?php echo $this->__('label.start_date') ?></label>
        <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for=""><?php echo $this->__('label.start_time') ?></label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="" />
           </div>
    </div>
    <div class="par">
        <label for="dateTo"><?php echo $this->__('label.end_date') ?></label>
        <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for=""><?php echo $this->__('label.end_time') ?> </label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="" />
           </div>
    </div>

    <label for="allDay"><?php echo $this->__('label.all_day') ?></label>
    <input type="checkbox" id="allDay" name="allDay"
    <?php if ($values['allDay'] === 'true') {
        echo 'checked="checked" ';
    }?>
    /><br /><br />

    <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>

    <p class="stdformbutton">
        <input type="hidden" value="1" name="save" />
        <input type="submit" name="saveEvent" id="saveEvent" value="<?php echo $this->__('buttons.save') ?>" class="button" />
    </p>

    <?php $this->dispatchTplEvent('beforeFormClose'); ?>

</form>

<script type="text/javascript">

    <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
