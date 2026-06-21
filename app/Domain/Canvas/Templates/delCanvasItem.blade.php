@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}">
    <p>{!! __('text.confirm_board_item_deletion') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas">{!! __('buttons.back') !!}</x-global::forms.button>
</form>
