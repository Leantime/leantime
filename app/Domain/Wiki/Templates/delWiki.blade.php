<x-globals::elements.section-title icon="delete">{{ __('buttons.delete') }}</x-globals::elements.section-title>

<form method="post" action="{{ BASE_URL }}/wiki/delWiki/{{ $tpl->escape($_GET['id']) }}">
    <p>{{ __('text.are_you_sure_delete_wiki') }}</p>
    <x-globals::forms.button :submit="true" state="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/wiki/show" contentRole="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
