<x-globals::elements.section-title>{{ __('headlines.delete_time') }}</x-globals::elements.section-title>

<form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $tpl->get('id') }}">
    <p>{{ __('text.confirm_delete_timesheet') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ session('lastPage') }}" type="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
