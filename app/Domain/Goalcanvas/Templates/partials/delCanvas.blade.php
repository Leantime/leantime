<x-global::content.modal.modal-buttons/>
<x-global::content.modal.header>
    Delete Goal
</x-global::content.modal.header>
<h4 class="widgettitle title-light">{!!__("subtitles.delete") !!}</h4>

<x-global::content.modal.form method="post" action="{{ BASE_URL."/goalcanvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <x-global::forms.button content-role="primary" type="submit" name="del">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>

    <x-global::forms.button content-role="secondary" tag="a" href="{{ BASE_URL . '/goalcanvas/showCanvas' }}">
        {{ __('buttons.back') }}
    </x-global::forms.button>

</x-global::content.modal.form>
