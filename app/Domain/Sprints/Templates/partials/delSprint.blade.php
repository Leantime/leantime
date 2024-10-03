<x-global::content.modal.modal-buttons/>

<x-global::content.modal.header class="widgettitle title-light">
    <?=$tpl->__("headlines.delete_sprint") ?>
</x-global::content.modal.header>


<x-global::content.modal.form action="{{ BASE_URL }}/sprints/delSprint/{{ $id }}">
    <p><?=$tpl->__("text.are_you_sure_delete_sprint") ?></p><br />
    <x-global::forms.button type="submit" name="del" class="btn">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ session('lastPage') }}" content-role="secondary">
        {{ __('buttons.back') }}
    </x-global::forms.button>
    
</x-global::content.modal.form>

