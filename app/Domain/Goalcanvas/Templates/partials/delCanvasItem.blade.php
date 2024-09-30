<x-global::content.modal.modal-buttons/>

<x-global::content.modal.header>
    Delete Goal item
</x-global::content.modal.header>
<h4 class="widgettitle title-light">{!! __("subtitles.delete") !!}</h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<x-global::content.modal.form method="post" action="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}">
    <p>{{ __('text.confirm_board_item_deletion') }}</p><br />
    <x-global::forms.button content-role="primary" type="submit" name="del">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>

    <x-global::forms.button content-role="secondary" tag="a" href="{{ BASE_URL }}/goalcanvas/showCanvas">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    >
</x-global::content.modal.form>
