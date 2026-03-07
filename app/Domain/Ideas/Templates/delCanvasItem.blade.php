<h4 class="widgettitle title-light"><x-global::elements.icon name="delete" /> {{ $tpl->__('buttons.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/ideas/delCanvasItem/{{ (int) $_GET['id'] }}">
    <p>{{ $tpl->__('text.are_you_sure_delete_idea') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/ideas/showBoards/" type="secondary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
</form>
