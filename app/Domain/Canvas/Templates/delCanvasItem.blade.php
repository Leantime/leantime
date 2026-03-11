@php
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
@endphp

<x-globals::elements.section-title icon="delete">{{ $tpl->__('label.delete') }}</x-globals::elements.section-title>
<hr class="tw:mt-1 tw:mb-4">

<form method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}">
    <p>{{ $tpl->__('text.confirm_board_item_deletion') }}</p><br />
    <x-globals::forms.button :submit="true" state="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas" contentRole="secondary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
</form>
