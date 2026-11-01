{!! $tpl->displayNotification() !!}

<x-global::actions.modal-header>
    <x-slot:title><i class="fa-regular fa-calendar-plus"></i> {{ __('label.import_ical') }}</x-slot:title>
</x-global::actions.modal-header>{!!  __('label.import_ical_content')  !!}


<form action="{{ BASE_URL }}/calendar/importGCal" method="post" class="formModal">

    <x-global::forms.field for="name" :label="__('label.calendar_name')">
        <x-global::forms.text-input id="name" name="name" autocomplete="off" value="{{ $values['name'] }}" />
    </x-global::forms.field>

    <x-global::forms.field for="url" :label="__('label.ical_url')">
        <x-global::forms.text-input width="lg" id="url" name="url" autocomplete="off" value="{{ $values['url'] }}" />
    </x-global::forms.field>

    <x-global::forms.field for="colorClass" :label="__('label.color')">
        <input type="text" id="colorClass" name="colorClass" autocomplete="off" value="{{ $values['colorClass'] }}"  class="simpleColorPicker"/>
    </x-global::forms.field>

    @dispatchEvent('beforeSubmitButton')
    <br />
    <x-global::forms.button tag="input" inputType="submit" name="save" id="save" :labelText="__('buttons.save')" />




    @dispatchEvent('beforeFormClose')

</form>

<script>
    leantime.ticketsController.initSimpleColorPicker();
</script>
