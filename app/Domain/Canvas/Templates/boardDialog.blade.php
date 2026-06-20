@php
    $canvasTitle = $canvasTitle ?? '';
    $canvasName = $canvasName ?? '';
@endphp

<form action="{{ BASE_URL }}/{{ $canvasName }}canvas/boardDialog{{ isset($_GET['id']) ? '/' . (int) $_GET['id'] : '' }}" method="post" class="formModal">
    <div class="modal-header">
        <h4 class="modal-title"><i class='fa fa-plus'></i> {!! __('subtitles.create_new_board') !!}</h4>
    </div>
    <div class="modal-body">
        <label>{!! __('label.title_new') !!}</label><br />
        <x-global::forms.text-input name="canvastitle" value="{{ $canvasTitle }}" placeholder="{{ __('input.placeholders.enter_title_for_board') }}"
               style="width: 100%" />
    </div>
    <div class="modal-footer">
        @if(isset($_GET['id']))
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save_board')" name="newCanvas" />
            <input type="hidden" name="editCanvas" value="{{ (int) $_GET['id'] }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.create_board')" name="newCanvas" />
        @endif
        <x-global::forms.button inputType="button" contentRole="tertiary" onclick="jQuery.nmTop().close();">{!! __('buttons.close') !!}</x-global::forms.button>
    </div>
</form>
