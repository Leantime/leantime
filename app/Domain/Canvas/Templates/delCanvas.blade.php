@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<h4 class="widgettitle title-light">{{ $tpl->__('subtitles.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvas/{{ $id }}">
    <p>{{ $tpl->__('text.confirm_board_deletion') }}</p><br />
    <input type="submit" value="{{ $tpl->__('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary"
       href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas">{{ $tpl->__('buttons.back') }}</a>
</form>
