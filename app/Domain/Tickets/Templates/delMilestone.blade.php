<h4 class="widgettitle title-light">{!! __('subtitles.delete_milestone') !!}</h4>

<form method="post" action="{{ BASE_URL }}/tickets/delMilestone/{{ $ticket->id }}">
    <p>{!! __('text.confirm_milestone_deletion') !!}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/tickets/roadmap/">{!! __('buttons.back') !!}</x-global::forms.button>
</form>
