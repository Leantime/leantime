<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get("ticket");
?>

<h4 class="widgettitle title-light">{{ __("subtitles.delete_milestone") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/tickets/delMilestone/{{ $ticket->id }}">
    <p>{{ __("text.confirm_milestone_deletion") }}</p><br />
    <x-global::forms.button type="submit" name="del" class="button">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ BASE_URL }}/tickets/roadmap/" content-role="secondary">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    
</x-global::content.modal.form>

