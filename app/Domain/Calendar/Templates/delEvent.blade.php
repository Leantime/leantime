@extends($layout)

@section('content')

@php
    $id = (int) $_GET['id'];
@endphp

<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>

<form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delEvent/{{ $id }}">
    @dispatchEvent('afterFormOpen')
    <p>{!! __('text.confirm_event_deletion') !!}</p><br />
    @dispatchEvent('beforeSubmitButton')
    <x-global::forms.button inputType="submit" contentRole="primary" id="saveAndClose" value="closeModal">{!! __('buttons.yes_delete') !!}</x-global::forms.button>
    <x-global::forms.button tag="a" contentRole="primary" link="{{ BASE_URL }}/calendar/showMyCalendar">{!! __('buttons.back') !!}</x-global::forms.button>
    @dispatchEvent('beforeFormClose')
</form>

@endsection
