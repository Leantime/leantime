<h4 class="widgettitle title-light">{{ __('headlines.delete_time') }}</h4>

<form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $tpl->get('id') }}">
    <p>{{ __('text.confirm_delete_timesheet') }}</p><br />
    <x-global::button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-global::button>
    <x-global::button link="{{ session('lastPage') }}" type="secondary">{{ __('buttons.back') }}</x-global::button>
</form>
