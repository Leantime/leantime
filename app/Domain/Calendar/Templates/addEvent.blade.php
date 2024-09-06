@extends($layout)

@section('content')

<?php

$values = $tpl->get('values');
?>


<?php echo $tpl->displayNotification() ?>

<h4 class="widgettitle title-light"><?php echo $tpl->__('subtitles.event'); ?></h4>

<form action="<?=BASE_URL?>/calendar/addEvent/" method="post" class='formModal'>

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

@endsection

