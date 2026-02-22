@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<h4 class="widgettitle title-light">{!! $tpl->__('subtitles.delete') !!}</h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}">
    <input type="hidden" name="del" value="1" />
    <p>{{ $tpl->__('text.confirm_board_item_deletion') }}</p><br />
    <x-global::button submit type="danger">{{ $tpl->__('buttons.yes_delete') }}</x-global::button>
    <x-global::button link="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas" type="secondary">{{ $tpl->__('buttons.back') }}</x-global::button>
</form>
