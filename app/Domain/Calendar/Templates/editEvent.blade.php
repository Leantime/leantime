@php
    $values = $tpl->get('values');
@endphp

{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light">{{ __('subtitles.event') }}</h4>

<form action="{{ BASE_URL }}/calendar/editEvent/{{ $values['id'] }}" method="post" class="formModal">

    @dispatchEvent('afterFormOpen')

    <label for="description">{{ __('label.title') }}</label>
    <input type="text" id="description" name="description" value="{{ e($values['description']) }}" /><br />

    <label for="dateFrom">{{ __('label.start_date') }}</label>
    <input type="text" id="event_date_from" autocomplete="off" name="dateFrom" value="{{ format($values['dateFrom'])->date() }}" />

    <div class="par">
        <label> {{ __('label.start_time') }}</label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_from" name="timeFrom" value="{{ format($values['dateFrom'])->time24() }}" />
        </div>
    </div>

    <label for="dateTo">{{ __('label.end_date') }}</label>
    <input type="text" id="event_date_to" autocomplete="off" name="dateTo" value="{{ format($values['dateTo'])->date() }}" />

    <div class="par">
        <label for="">{{ __('label.end_time') }} </label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_to" name="timeTo" value="{{ format($values['dateTo'])->time24() }}" />
        </div>
    </div>

    <label for="allDay">{{ __('label.all_day') }}</label>
    <input type="checkbox" id="allDay" name="allDay"
        @if($values['allDay'] === 'true') checked="checked" @endif
    />

    @dispatchEvent('beforeSubmitButton')

    <div class="clear"></div>
    <br />
    <a href="{{ BASE_URL }}/calendar/delEvent/{{ (int) $_GET['id'] }}" class="formModal delete right"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a>
    <input type="hidden" value="1" name="save" />
    <input type="submit" name="saveEvent" id="save" value="{{ __('buttons.save') }}" class="button" />

    <div class="clear"></div>

    @dispatchEvent('beforeFormClose')

</form>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });
</script>
