<x-global::content.modal.modal-buttons/>

<?php
$values = $tpl->get('values');
?>


@displayNotification()

<h4 class="widgettitle title-light">{{ __("subtitles.event") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/calendar/addEvent/">

    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

    <label for="description">{{ __("label.title") }}</label>
    <input type="text" id="description" name="description" value="<?php $tpl->e($values['description']); ?>" /><br />

    <div class="par">
        <label for="dateFrom">{{ __("label.start_date") }}</label>
        <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for="">{{ __("label.start_time") }}</label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="" />
           </div>
    </div>
    <div class="par">
        <label for="dateTo">{{ __("label.end_date") }}</label>
        <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for="">{{ __("label.end_time") }} </label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="" />
           </div>
    </div>

    <label for="allDay">{{ __("label.all_day") }}</label>
    <input type="checkbox" id="allDay" name="allDay"
    <?php if (isset($values['allDay']) === true && $values['allDay'] === true) {
        echo 'checked="checked" ';
    }?>
    /><br /><br />

    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

    <p class="stdformbutton">
        <input type="hidden" value="1" name="save" />
        <input type="submit" name="saveEvent" id="saveEvent" value="{{ __("buttons.save") }}" class="button" />
    </p>

    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

</x-global::content.modal.form>

<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
