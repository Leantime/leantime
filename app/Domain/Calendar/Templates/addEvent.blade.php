@php
    $values = $tpl->get('values');
@endphp

{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light">{{ __('subtitles.event') }}</h4>

<form action="{{ BASE_URL }}/calendar/addEvent/" method="post" class="formModal">

    @dispatchEvent('afterFormOpen')

    <label for="description">{{ __('label.title') }}</label>
    <x-global::forms.input name="description" id="description" value="{{ e($values['description']) }}" /><br />

    <div class="par">
        <label for="dateFrom">{{ __('label.start_date') }}</label>
        <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for="">{{ __('label.start_time') }}</label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_from" name="timeFrom" value="" />
        </div>
    </div>
    <div class="par">
        <label for="dateTo">{{ __('label.end_date') }}</label>
        <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <label for="">{{ __('label.end_time') }} </label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_to" name="timeTo" value="" />
        </div>
    </div>

    <label for="allDay">{{ __('label.all_day') }}</label>
    <input type="checkbox" id="allDay" name="allDay"
        @if(isset($values['allDay']) && $values['allDay'] === true) checked="checked" @endif
    /><br /><br />

    @dispatchEvent('beforeSubmitButton')

    <p class="stdformbutton">
        <input type="hidden" value="1" name="save" />
        <x-global::button submit type="primary" name="saveEvent" id="saveEvent">{{ __('buttons.save') }}</x-global::button>
    </p>

    @dispatchEvent('beforeFormClose')

</form>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    @dispatchEvent('scripts.beforeClose')

</script>
