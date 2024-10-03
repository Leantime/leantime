<x-global::content.modal.modal-buttons/>

<?php
$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
?>

<x-global::content.modal.header>
    Del {{$canvasName}}
</x-global::content.modal.header>

<h4 class="widgettitle title-light"><?=$tpl->__("subtitles.delete") ?></h4>


<x-global::content.modal.form action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvas/{{ $id }}">
    <p>{{ __("text.confirm_board_deletion") }}</p><br />
    <x-global::forms.button type="submit" name="del" class="button">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas" content-role="secondary">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    
</x-global::content.modal.form >


