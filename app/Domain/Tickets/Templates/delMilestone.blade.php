<x-global::actions.modal-header>
    <x-slot:title>{!! __('subtitles.delete_milestone') !!}</x-slot:title>
</x-global::actions.modal-header>
<form method="post" action="{{ BASE_URL }}/tickets/delMilestone/{{ $ticket->id }}">
    <p>{!! __('text.confirm_milestone_deletion') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/tickets/roadmap/">{!! __('buttons.back') !!}</x-global::forms.button>
</form>
