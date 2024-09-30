@extends($layout)

@section('content')

<?php
$id = (int) $_GET['id'];
?>

<x-global::content.modal.modal-buttons/>

<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<x-global::content.modal.form method="post" class="formModal" action="{{ BASE_URL }}/calendar/delEvent/{{ $id }}">
    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
    <p>{{ __("text.confirm_event_deletion") }}</p><br />
    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
    <x-global::forms.button type="submit" id="saveAndClose" value="closeModal">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ BASE_URL }}/calendar/showMyCalendar">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
</x-global::content.modal.form>

