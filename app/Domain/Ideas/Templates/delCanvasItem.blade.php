<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {{ $tpl->__('buttons.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/ideas/delCanvasItem/{{ (int) $_GET['id'] }}">
    <p>{{ $tpl->__('text.are_you_sure_delete_idea') }}</p><br />
    <x-global::button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-global::button>
    <x-global::button link="{{ BASE_URL }}/ideas/showBoards/" type="secondary">{{ $tpl->__('buttons.back') }}</x-global::button>
</form>
