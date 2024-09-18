<x-global::content.modal.modal-buttons/>

<?php
$id = (int) $_GET['id'];
?>

<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/calendar/delExternalCalendar/{{ $id }}">
    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
    <p>{{ __("text.confirm_calendar_deletion") }}</p><br />
    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
    <x-global::forms.button type="submit" id="saveAndClose" value="closeModal">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ BASE_URL }}/calendar/showMyCalendar">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    
    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
</x-global::content.modal.form>

