@php
    $allCanvas = $tpl->get('allCanvas');
    $canvasTitle = $tpl->get('canvasTitle');
    $canvasName = $tpl->get('canvasName');
@endphp

<form action="{{ BASE_URL }}/ideas/boardDialog{{ isset($_GET['id']) ? '/' . (int) $_GET['id'] : '' }}" method="post" class="formModal">
    <div style="margin-bottom: 15px;">
        <h4 class="widgettitle title-light"><i class="fa fa-plus" aria-hidden="true"></i> {{ $tpl->__('subtitles.create_new_board') }}</h4>
    </div>
    <div style="margin-bottom: 15px;">
        <label>{{ $tpl->__('label.title_new') }}</label><br />
        <x-globals::forms.input name="canvastitle" value="{{ $tpl->escape($canvasTitle) }}" placeholder="{{ $tpl->__('input.placeholders.enter_title_for_board') }}"
               style="width: 100%" />
    </div>
    <div style="display: flex; justify-content: flex-end; gap: 8px; padding-top: 10px;">
        @if (isset($_GET['id']))
            <x-globals::forms.button submit type="primary" name="newCanvas">{{ $tpl->__('buttons.save_board') }}</x-globals::forms.button>
            <input type="hidden" name="editCanvas" value="{{ (int) ($_GET['id'] ?? '') }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <x-globals::forms.button submit type="primary" name="newCanvas">{{ $tpl->__('buttons.create_board') }}</x-globals::forms.button>
        @endif
        <x-globals::forms.button tag="button" type="secondary" onclick="leantime.modals.closeModal();">{{ $tpl->__('buttons.close') }}</x-globals::forms.button>
    </div>
</form>
