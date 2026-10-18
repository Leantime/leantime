@extends($layout)

@section('content')

@php
    $values = $values ?? [];
@endphp

{!! $tpl->displayNotification() !!}

<x-global::actions.modal-header>
    <x-slot:title>{!! __('subtitles.event') !!}</x-slot:title>
</x-global::actions.modal-header>
<form action="{{ BASE_URL }}/calendar/editEvent/{{ $values['id'] }}" method="post" class="formModal">

    @dispatchEvent('afterFormOpen')

    <x-global::forms.label for="description">{!! __('label.title') !!}</x-global::forms.label>
    <x-global::forms.text-input id="description" name="description" value="{{ $tpl->escape($values['description']) }}" /><br />

    <x-global::forms.label for="dateFrom">{!! __('label.start_date') !!}</x-global::forms.label>
    <input type="text" id="event_date_from" autocomplete="off" name="dateFrom" value="{{ format($values['dateFrom'])->date() }}" />

    <div class="par">
        <x-global::forms.label> {!! __('label.start_time') !!}</x-global::forms.label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_from" name="timeFrom" value="{{ format($values['dateFrom'])->time24() }}" />
           </div>
    </div>

    <x-global::forms.label for="dateTo">{!! __('label.end_date') !!}</x-global::forms.label>
    <input type="text" id="event_date_to" autocomplete="off" name="dateTo" value="{{ format($values['dateTo'])->date() }}" />

    <div class="par">
        <x-global::forms.label for="">{!! __('label.end_time') !!} </x-global::forms.label>
        <div class="input-append bootstrap-timepicker">
                <input type="time" id="event_time_to" name="timeTo" value="{{ format($values['dateTo'])->time24() }}" />
           </div>
    </div>

    <label for="allDay">{!! __('label.all_day') !!}</label>
    <input type="checkbox" id="allDay" name="allDay"
    @if ($values['allDay'] === 'true')
        checked="checked"
    @endif
    />

    @dispatchEvent('beforeSubmitButton')

    <div class="clear"></div>
    <br />
    <x-global::forms.button tag="a" link="{{ BASE_URL }}/calendar/delEvent/{{ (int) $_GET['id'] }}" class="formModal delete right" state="danger" variant="outline"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</x-global::forms.button>
    <input type="hidden" value="1" name="save" />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="saveEvent" id="save" />

    <div class="clear"></div>

    @dispatchEvent('beforeFormClose')

</form>

@once
@push('scripts')
<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });
</script>
@endpush
@endonce

@endsection
