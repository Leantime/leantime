<x-global::content.modal.modal-buttons/>

<x-global::content.modal.header>
    <i class="fa fa-trash"></i> {!! __("buttons.delete") !!}
</x-global::content.modal.header>

<x-global::content.modal.form action="{{ BASE_URL }}/ideas/delCanvasItem/{{ (int) $_GET['id'] }}">
    <p>{!! __('text.are_you_sure_delete_idea') !!}</p><br />
    <x-global::forms.button type="submit" name="del" class="button">
        {!! __('buttons.yes_delete') !!}
    </x-global::forms.button>

    <x-global::forms.button tag="a" href="{{ BASE_URL }}/ideas/showBoards/" class="btn btn-secondary"
        content-role="secondary">
        {!! __('buttons.back') !!}
    </x-global::forms.button>
</x-global::content.modal.form>

@endsection
