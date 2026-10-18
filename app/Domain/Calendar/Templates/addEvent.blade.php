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

    <x-global::forms.label for="description">{!! __('label.title') !!}</x-global::forms.label>
    <x-global::forms.text-input id="description" name="description" value="{{ $tpl->escape($values['description']) }}" /><br />

    <div class="par">
        <x-global::forms.label for="dateFrom">{!! __('label.start_date') !!}</x-global::forms.label>
        <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <x-global::forms.label for="">{!! __('label.start_time') !!}</x-global::forms.label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="" />
           </div>
    </div>
    <div class="par">
        <x-global::forms.label for="dateTo">{!! __('label.end_date') !!}</x-global::forms.label>
        <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" /><br/>
    </div>
    <div class="par">
        <x-global::forms.label for="">{!! __('label.end_time') !!} </x-global::forms.label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="" />
           </div>
    </div>

    <label for="allDay">{!! __('label.all_day') !!}</label>
    <input type="checkbox" id="allDay" name="allDay"
    @if (isset($values['allDay']) && $values['allDay'] === true)
        checked="checked"
    @endif
    /><br /><br />

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
