@php
    $id = (int) $_GET['id'];
@endphp

<h4 class="widgettitle title-light"><x-global::elements.icon name="delete" /> {{ __('label.delete') }}</h4>

<form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delEvent/{{ $id }}">
    @dispatchEvent('afterFormOpen')
    <p>{{ __('text.confirm_event_deletion') }}</p><br />
    @dispatchEvent('beforeSubmitButton')
    <x-globals::forms.button submit type="danger" id="saveAndClose" value="closeModal">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/calendar/showMyCalendar" type="primary">{{ __('buttons.back') }}</x-globals::forms.button>
    @dispatchEvent('beforeFormClose')
</form>
