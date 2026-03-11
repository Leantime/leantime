
<x-globals::elements.section-title>{{ __('headlines.delete_sprint') }}</x-globals::elements.section-title>

<form method="post" action="{{ BASE_URL }}/sprints/delSprint/{{ $tpl->get('id') }}">
    <p>{{ __('text.are_you_sure_delete_sprint') }}</p><br />
    <x-globals::forms.button :submit="true" state="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ session('lastPage') }}" contentRole="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
