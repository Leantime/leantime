@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<h4 class="widgettitle title-light">{{ $tpl->__('subtitles.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvas/{{ $id }}">
    <p>{{ $tpl->__('text.confirm_board_deletion') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas" type="secondary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
</form>
