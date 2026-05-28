@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>

<form method="post" action="{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/delCanvas/{{ $id }}">
    <p>{!! __('text.confirm_board_deletion') !!}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary"
       href="{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/showCanvas">{!! __('buttons.back') !!}</a>
</form>
