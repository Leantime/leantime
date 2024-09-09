<x-global::content.modal.modal-buttons/>

<?php
$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
?>


<h4 class="widgettitle title-light"><?=$tpl->__("subtitles.delete") ?></h4>


<x-global::content.modal.form action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvas/{{ $id }}">
    <p>{{ __("text.confirm_board_deletion") }}</p><br />
    <input type="submit" value="{{ __("buttons.yes_delete") }}" name="del" class="button" />
    <a class="btn btn-secondary"
       href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas">{{ __("buttons.back") }}</a>
</x-global::content.modal.form >


