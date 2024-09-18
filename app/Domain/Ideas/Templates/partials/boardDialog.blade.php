<x-global::content.modal.modal-buttons/>

<x-global::content.modal.form action="{{ BASE_URL }}/ideas/boardDialog{{ isset($_GET['id']) ? '/'.(int)$_GET['id'] : '' }}">
    <div class="modal-header">
        <h4 class="modal-title"><i class='fa fa-plus'></i> {!! __('subtitles.create_new_board') !!}</h4>
    </div>
    <div class="modal-body">
        <label>{!! __("label.title_new") !!}</label><br />
        <input type="text" name="canvastitle" value="{{ $canvasTitle }}" placeholder="{!! __("input.placeholders.enter_title_for_board") !!}"
               style="width: 100%"/>
    </div>
    <div class="modal-footer">
        @if (isset($_GET['id']))
            <x-global::forms.button type="submit" class="btn btn-primary" name="newCanvas">
                {!! __('buttons.save_board') !!}
            </x-global::forms.button>

            <input type="hidden" name="editCanvas" value="{{ (int) ($_GET['id'] ?? '') }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <x-global::forms.button type="submit" class="btn btn-primary" name="newCanvas">
                {!! __('buttons.create_board') !!}
            </x-global::forms.button>
        @endif
        <x-global::forms.button type="button" class="btn btn-default" onclick="jQuery.nmTop().close();"
            content-role="secondary">
            {!! __('buttons.close') !!}
        </x-global::forms.button>

    </div>
</x-global::content.modal.form>
