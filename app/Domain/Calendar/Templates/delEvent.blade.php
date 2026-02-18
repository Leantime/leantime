@php
    $id = (int) $_GET['id'];
@endphp

<h4 class="widgettitle title-light">{{ __('subtitles.delete') }}</h4>

<form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delEvent/{{ $id }}">
    @dispatchEvent('afterFormOpen')
    <p>{{ __('text.confirm_event_deletion') }}</p><br />
    @dispatchEvent('beforeSubmitButton')
    <x-global::button submit type="danger" id="saveAndClose" value="closeModal">{{ __('buttons.yes_delete') }}</x-global::button>
    <x-global::button link="{{ BASE_URL }}/calendar/showMyCalendar" type="primary">{{ __('buttons.back') }}</x-global::button>
    @dispatchEvent('beforeFormClose')
</form>
