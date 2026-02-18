<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {{ __('buttons.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/wiki/delWiki/{{ $tpl->escape($_GET['id']) }}">
    <p>{{ __('text.are_you_sure_delete_wiki') }}</p>
    <x-global::button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-global::button>
    <x-global::button link="{{ BASE_URL }}/wiki/show" type="secondary">{{ __('buttons.back') }}</x-global::button>
</form>
