@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvas/{{ $id }}">
    <p>{!! __('text.confirm_board_deletion') !!}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <x-global::forms.button tag="a" contentRole="tertiary"
       link="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas">{!! __('buttons.back') !!}</x-global::forms.button>
</form>
