<x-global::content.modal.modal-buttons />

@displayNotification()

<h4 class="widgettitle title-light">{{ __("subtitles.event") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/calendar/editEvent/{{ $values['id'] }}">

    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

    <x-global::forms.text-input 
        type="text" 
        id="description" 
        name="description" 
        labelText="{{ __('label.title') }}" 
        value="{{ $tpl->escape($values['description']) }}" 
    />

    <x-global::forms.text-input 
        type="text" 
        id="event_date_from" 
        name="dateFrom" 
        labelText="{{ __('label.start_date') }}" 
        value="{{ format($values['dateFrom'])->date() }}" 
        autocomplete="off" 
    />

    <div class="par">
        <label> {{ __("label.start_time") }}</label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="<?php echo format($values['dateFrom'])->time24(); ?>" />
           </div>
    </div>

    <label for="dateTo">{{ __("label.end_date") }}</label>
    <input type="text" id="event_date_to" autocomplete="off" name="dateTo" value="<?php echo format($values['dateTo'])->date(); ?>" />

    <div class="par">
        <label for="">{{ __("label.end_time") }} </label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="<?php echo format($values['dateTo'])->time24(); ?>" />
           </div>
    </div>


    <x-global::forms.checkbox
        name="allDay"
        id="allDay"
        :checked="$values['allDay']"
        labelText="{{ __('label.all_day') }}"
        labelPosition="right"
    />

    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

    <div class="clear"></div>
    <br />
    <a href="#/calendar/delEvent/{{ $values['id'] }}"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete')?></a>
    <input type="hidden" value="1" name="save" />
    <x-global::forms.button type="submit" name="saveEvent" id="save" class="button">
        {{ __('buttons.save') }}
    </x-global::forms.button>

    <div class="clear"></div>

    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

</x-global::content.modal.form>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });
</script>
