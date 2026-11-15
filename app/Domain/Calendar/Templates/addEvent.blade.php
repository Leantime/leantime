@extends($layout)

@section('content')

@php
    $values = $values ?? [];
@endphp

{!! $tpl->displayNotification() !!}

<x-global::actions.modal-header>
    <x-slot:title>{!! __('subtitles.event') !!}</x-slot:title>
</x-global::actions.modal-header>
<form action="{{ BASE_URL }}/calendar/addEvent/" method="post" class='formModal'>
    @csrf

    @dispatchEvent('afterFormOpen')

    <x-global::forms.field for="description" :label="__('label.title')">
        <x-global::forms.text-input id="description" name="description" value="{{ $tpl->escape($values['description']) }}" />
    </x-global::forms.field>

    <x-global::forms.field for="event_date_from" :label="__('label.start_date')">
        <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" />
    </x-global::forms.field>

    <x-global::forms.field for="event_time_from" :label="__('label.start_time')">
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_from" name="timeFrom" value="" />
        </div>
    </x-global::forms.field>

    <x-global::forms.field for="event_date_to" :label="__('label.end_date')">
        <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" />
    </x-global::forms.field>

    <x-global::forms.field for="event_time_to" :label="__('label.end_time')">
        <div class="input-append bootstrap-timepicker">
            <input type="time" id="event_time_to" name="timeTo" value="" />
        </div>
    </x-global::forms.field>

    <x-global::forms.checkbox variant="toggle" id="allDay" name="allDay" value="on"
        :checked="isset($values['allDay']) && $values['allDay'] === true">{!! __('label.all_day') !!}</x-global::forms.checkbox><br /><br />

    @dispatchEvent('beforeSubmitButton')

    <p class="stdformbutton">
        <input type="hidden" value="1" name="save" />
        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="saveEvent" id="saveEvent" />
    </p>

    @dispatchEvent('beforeFormClose')

</form>

@once
@push('scripts')
<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    @dispatchEvent('scripts.beforeClose')

</script>
@endpush
@endonce

@endsection
