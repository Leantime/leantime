<x-global::content.modal.modal-buttons/>

<x-global::content.modal.header>
    Idea Board
</x-global::content.modal.header>

<x-global::content.modal.form action="{{ BASE_URL }}/ideas/boardDialog{{ isset($_GET['id']) ? '/'.(int)$_GET['id'] : '' }}">

    <div class="modal-body">
        <x-global::forms.text-input
        type="text"
        name="canvastitle"
        value="{{ $canvasTitle }}"
        labelText="{{ __('label.title_new') }}"
        placeholder="{{ __('input.placeholders.enter_title_for_board') }}"
        variant="title"
    />

    </div>
    <div class="modal-footer">
        @if(isset($_GET['id']))
            <x-global::forms.button type="submit" content-role="primary" name="newCanvas">
                {!! __('buttons.save_board') !!}
            </x-global::forms.button>
            <input type="hidden" name="editCanvas" value="{{ (int)$_GET['id'] ?? '' }}">
        @else
            <input type="hidden" name="newCanvas" value="true">
            <x-global::forms.button type="submit" content-role="primary" name="newCanvas">
                {!! __('buttons.create_board') !!}
            </x-global::forms.button>
        @endif

        <x-global::forms.button type="button" content-role="secondary" data-dismiss="modal">
            {!! __('buttons.close') !!}
        </x-global::forms.button>
    </div>
</x-global::content.modal.form>
