<h4 class="widgettitle title-light">{{ __('headlines.delete_time') }}</h4>

<form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $tpl->get('id') }}">
    <p>{{ __('text.confirm_delete_timesheet') }}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ session('lastPage') }}">{{ __('buttons.back') }}</a>
</form>
