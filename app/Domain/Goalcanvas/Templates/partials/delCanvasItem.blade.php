<x-global::content.modal.modal-buttons/>
<h4 class="widgettitle title-light">{!! __("subtitles.delete") !!}</h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<x-global::content.modal.form method="post" action="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}">
    <p>{{ __('text.confirm_board_item_deletion') }}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL }}/goalcanvas/showCanvas">{{ __('buttons.back') }}</a>
</x-global::content.modal.form>

