<x-global::content.modal.modal-buttons/>

<?php
$values = $tpl->get('values');
?>


@displayNotification()

<h4 class="widgettitle title-light">{{ __("subtitles.event") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/calendar/addEvent/">

    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

    <x-global::forms.text-input
        inputType="text"
        id="description"
        name="description"
        size='md'
        placeholder=""
        label="{{__('label.title')}}"
        value="$values['description']"
    />

    <x-global::forms.text-input
        inputType="text"
        id="event_date_from"
        name="dateFrom"
        size='md'
        placeholder=""
        label="{{__('label.start_date')}}"
        value=""
    />

    <x-global::forms.text-input
        inputType="time"
        id="event_time_from"
        name="timeFrom"
        placeholder=""
        label="{{ __('label.start_time') }}"
        value=""
    />

    <x-global::forms.text-input
        inputType="text"
        id="event_date_to"
        name="dateFrom"
        size='md'
        placeholder=""
        label="{{__('label.end_date')}}"
        value=""
    />


    <x-global::forms.text-input
        inputType="time"
        id="event_time_to"
        name="timeTo"
        placeholder=""
        label="{{ __('label.end_time') }}"
        value=""
    />



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
