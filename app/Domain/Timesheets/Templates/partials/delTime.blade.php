<x-global::content.modal.modal-buttons/>

<h4 class="widgettitle title-light"><?php printf("" . $tpl->__('headlines.delete_time') . ""); ?></h4>

<x-global::content.modal.form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $id }}">
    <p><?=$tpl->__("text.confirm_delete_timesheet") ?></p><br />
    <x-global::forms.button type="submit" name="del" class="button">
        {{ __('buttons.yes_delete') }}
    </x-global::forms.button>
    
    <x-global::forms.button tag="a" href="{{ session('lastPage') }}" content-role="secondary">
        {{ __('buttons.back') }}
    </x-global::forms.button>
</x-global::content.modal.form>

