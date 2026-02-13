@php
    $id = (int) $_GET['id'];
@endphp

<h4 class="widgettitle title-light">{{ __('subtitles.delete') }}</h4>

<form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delExternalCalendar/{{ $id }}">
    @dispatchEvent('afterFormOpen')
    <p>{{ __('text.confirm_calendar_deletion') }}</p><br />
    @dispatchEvent('beforeSubmitButton')
    <button type="submit" class="btn btn-primary" id="saveAndClose" value="closeModal">{{ __('buttons.yes_delete') }}</button>
    <a class="btn btn-primary" href="{{ BASE_URL }}/calendar/showMyCalendar">{{ __('buttons.back') }}</a>
    @dispatchEvent('beforeFormClose')
</form>
