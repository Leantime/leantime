@php
    $values = $tpl->get('values');
@endphp

{!! $tpl->displayNotification() !!}

<x-globals::elements.section-title>{{ __('subtitles.event') }}</x-globals::elements.section-title>

<form action="{{ BASE_URL }}/calendar/addEvent/" method="post" class="formModal">

    @dispatchEvent('afterFormOpen')

    <label for="description">{{ __('label.title') }}</label>
    <x-globals::forms.text-input name="description" id="description" value="{{ e($values['description']) }}" /><br />

    <div class="par">
        <label for="dateFrom">{{ __('label.start_date') }}</label>
        <x-globals::forms.date name="dateFrom" id="event_date_from" value="" /><br/>
    </div>
    <div class="par">
        <label for="">{{ __('label.start_time') }}</label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_from" name="timeFrom" value="" />
        </div>
    </div>
    <div class="par">
        <label for="dateTo">{{ __('label.end_date') }}</label>
        <x-globals::forms.date name="dateTo" id="event_date_to" value="" /><br/>
    </div>
    <div class="par">
        <label for="">{{ __('label.end_time') }} </label>
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_to" name="timeTo" value="" />
        </div>
    </div>

    <x-globals::forms.checkbox name="allDay" label="{{ __('label.all_day') }}"
        :checked="isset($values['allDay']) && $values['allDay'] === true" /><br /><br />

    @dispatchEvent('beforeSubmitButton')

    <input type="hidden" value="1" name="save" />
    <x-globals::forms.button :submit="true" contentRole="primary" name="saveEvent" id="saveEvent">{{ __('buttons.save') }}</x-globals::forms.button>

    @dispatchEvent('beforeFormClose')

</form>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    @dispatchEvent('scripts.beforeClose')

</script>
