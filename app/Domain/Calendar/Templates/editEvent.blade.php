@php
    $values = $tpl->get('values');
@endphp

{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light">{{ __('subtitles.event') }}</h4>

<form action="{{ BASE_URL }}/calendar/editEvent/{{ $values['id'] }}" method="post" class="formModal">

    @dispatchEvent('afterFormOpen')

    <label for="description">{{ __('label.title') }}</label>
    <x-globals::forms.input name="description" id="description" value="{{ e($values['description']) }}" /><br />

    <label for="dateFrom">{{ __('label.start_date') }}</label>
    <x-globals::forms.date name="dateFrom" id="event_date_from" value="{{ format($values['dateFrom'])->date() }}" />

    <div class="par">
        <label> {{ __('label.start_time') }}</label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_from" name="timeFrom" value="{{ format($values['dateFrom'])->time24() }}" />
        </div>
    </div>

    <label for="dateTo">{{ __('label.end_date') }}</label>
    <x-globals::forms.date name="dateTo" id="event_date_to" value="{{ format($values['dateTo'])->date() }}" />

    <div class="par">
        <label for="">{{ __('label.end_time') }} </label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_to" name="timeTo" value="{{ format($values['dateTo'])->time24() }}" />
        </div>
    </div>

    <x-globals::forms.checkbox name="allDay" label="{{ __('label.all_day') }}"
        :checked="$values['allDay'] === 'true'" />

    @dispatchEvent('beforeSubmitButton')

    <div class="clear"></div>
    <br />
    <a href="{{ BASE_URL }}/calendar/delEvent/{{ (int) $_GET['id'] }}" class="formModal delete right"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a>
    <input type="hidden" value="1" name="save" />
    <x-globals::forms.button submit type="primary" name="saveEvent" id="save">{{ __('buttons.save') }}</x-globals::forms.button>

    <div class="clear"></div>

    @dispatchEvent('beforeFormClose')

</form>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });
</script>
