@extends($layout)

@section('content')

    <?php
$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
?>

<h4 class="widgettitle title-light"><?=$tpl->__("subtitles.delete") ?></h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/<?=$id ?>">
    <p>{{ __("text.confirm_board_item_deletion") }}</p><br />
    <x-global::forms.button type="submit" name="del" class="button">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas" class="btn btn-secondary">
        {{ __('buttons.back') }}
    </x-global::forms.button>
</form>
