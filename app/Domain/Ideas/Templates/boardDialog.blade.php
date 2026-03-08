@php
    $allCanvas = $tpl->get('allCanvas');
    $canvasTitle = $tpl->get('canvasTitle');
    $canvasName = $tpl->get('canvasName');
@endphp

<form action="{{ BASE_URL }}/ideas/boardDialog{{ isset($_GET['id']) ? '/' . (int) $_GET['id'] : '' }}" method="post" class="formModal">
    <div class="tw:mb-4">
        <x-globals::elements.section-title icon="add">{{ $tpl->__('subtitles.create_new_board') }}</x-globals::elements.section-title>
    </div>
    <div class="tw:mb-4">
        <label>{{ $tpl->__('label.title_new') }}</label><br />
        <x-globals::forms.text-input name="canvastitle" value="{{ $tpl->escape($canvasTitle) }}" placeholder="{{ $tpl->__('input.placeholders.enter_title_for_board') }}"
               class="tw:w-full" />
    </div>
    <div class="tw:flex tw:justify-end tw:gap-2 tw:pt-2">
        @if (isset($_GET['id']))
            <x-globals::forms.button :submit="true" contentRole="primary" name="newCanvas">{{ $tpl->__('buttons.save_board') }}</x-globals::forms.button>
            <input type="hidden" name="editCanvas" value="{{ (int) ($_GET['id'] ?? '') }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <x-globals::forms.button :submit="true" contentRole="primary" name="newCanvas">{{ $tpl->__('buttons.create_board') }}</x-globals::forms.button>
        @endif
        <x-globals::forms.button tag="button" contentRole="secondary" onclick="leantime.modals.closeModal();">{{ $tpl->__('buttons.close') }}</x-globals::forms.button>
    </div>
</form>
