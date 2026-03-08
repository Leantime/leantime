@php
    $id = (int) $_GET['id'];
@endphp

<x-globals::elements.section-title icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>

<form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delEvent/{{ $id }}">
    @dispatchEvent('afterFormOpen')
    <p>{{ __('text.confirm_event_deletion') }}</p><br />
    @dispatchEvent('beforeSubmitButton')
    <x-globals::forms.button :submit="true" state="danger" id="saveAndClose" value="closeModal">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button element="a" href="{{ BASE_URL }}/calendar/showMyCalendar" contentRole="primary">{{ __('buttons.back') }}</x-globals::forms.button>
    @dispatchEvent('beforeFormClose')
</form>
