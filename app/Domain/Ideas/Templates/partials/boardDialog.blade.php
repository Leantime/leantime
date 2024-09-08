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
        @if(isset($_GET['id']))
            <input type="submit" class="btn btn-primary" value="{!! __('buttons.save_board') !!}" name="newCanvas" />
            <input type="hidden" name="editCanvas" value="{{ (int)$_GET['id'] ?? '' }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <input type="submit" class="btn btn-primary" value="{!! __('buttons.create_board') !!}" name="newCanvas" />
        @endif
        <button type="button" class="btn btn-default" onclick="jQuery.nmTop().close();">{!! __('buttons.close') !!}</button>
    </div>
</x-global::content.modal.form>
