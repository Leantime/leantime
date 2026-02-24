
<h4 class="widgettitle title-light">{{ __('headlines.delete_sprint') }}</h4>

<form method="post" action="{{ BASE_URL }}/sprints/delSprint/{{ $tpl->get('id') }}">
    <p>{{ __('text.are_you_sure_delete_sprint') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ session('lastPage') }}" type="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
