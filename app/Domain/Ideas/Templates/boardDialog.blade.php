@php
    $allCanvas = $tpl->get('allCanvas');
    $canvasTitle = $tpl->get('canvasTitle');
    $canvasName = $tpl->get('canvasName');
@endphp

<form action="{{ BASE_URL }}/ideas/boardDialog{{ isset($_GET['id']) ? '/' . (int) $_GET['id'] : '' }}" method="post" class="formModal">
    <div class="modal-header">
        <h4 class="modal-title"><i class="fa fa-plus"></i> {{ $tpl->__('subtitles.create_new_board') }}</h4>
    </div>
    <div class="modal-body">
        <label>{{ $tpl->__('label.title_new') }}</label><br />
        <x-global::forms.input name="canvastitle" value="{{ $tpl->escape($canvasTitle) }}" placeholder="{{ $tpl->__('input.placeholders.enter_title_for_board') }}"
               style="width: 100%" />
    </div>
    <div class="modal-footer">
        @if (isset($_GET['id']))
            <x-global::button submit type="primary" name="newCanvas">{{ $tpl->__('buttons.save_board') }}</x-global::button>
            <input type="hidden" name="editCanvas" value="{{ (int) ($_GET['id'] ?? '') }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <x-global::button submit type="primary" name="newCanvas">{{ $tpl->__('buttons.create_board') }}</x-global::button>
        @endif
        <x-global::button tag="button" type="secondary" onclick="jQuery.nmTop().close();">{{ $tpl->__('buttons.close') }}</x-global::button>
    </div>
</form>
