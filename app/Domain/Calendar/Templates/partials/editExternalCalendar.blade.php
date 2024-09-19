<x-global::content.modal.modal-buttons/>

@displayNotification()

<h4 class="widgettitle title-light"><i class="fa-regular fa-calendar-plus"></i> {{ __('label.edit_ical') }}</h4>
{!!  __('label.import_ical_content')  !!}


<x-global::content.modal.form action="{{ BASE_URL }}/calendar/editExternal/{{ $values['id'] }}">

<input type="hidden" name="save" value="1" />

    <x-global::forms.text-input 
        type="text" 
        id="name" 
        name="name" 
        autocomplete="off" 
        labelText="{{ $tpl->__('label.calendar_name') }}" 
        value="{{ $values['name'] }}" 
    />

    <x-global::forms.text-input 
        type="text" 
        id="url" 
        name="url" 
        autocomplete="off" 
        labelText="{{ $tpl->__('label.ical_url') }}" 
        value="{{ $values['url'] }}" 
        class="w-[300px]" 
    />

    <x-global::forms.text-input 
        type="text" 
        name="colorClass" 
        labelText="{{ $tpl->__('label.color') }}" 
        autocomplete="off" 
        value="{{ $values['colorClass'] }}" 
        class="simpleColorPicker" 
    />

    @dispatchEvent('beforeSubmitButton')

    <br /><br />

    <x-global::forms.button 
        type="submit" 
        name="save" 
        id="save" 
        content-role="primary"
    >
        {{ __('buttons.save') }}
    </x-global::forms.button>




    @dispatchEvent('beforeFormClose')

</x-global::content.modal.form>

<script>
    leantime.ticketsController.initSimpleColorPicker();
</script>
