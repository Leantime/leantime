{!! $tpl->displayNotification() !!}

<x-global::actions.modal-header>
    <x-slot:title><i class="fa-regular fa-calendar-plus"></i> {{ __('label.import_ical') }}</x-slot:title>
</x-global::actions.modal-header>{!!  __('label.import_ical_content')  !!}


<form action="{{ BASE_URL }}/calendar/importGCal" method="post" class="formModal">

    <x-global::forms.label for="name">{{ $tpl->__('label.calendar_name') }}:</x-global::forms.label>
    <x-global::forms.text-input id="name" name="name" autocomplete="off" value="{{ $values['name'] }}" /><br />

    <x-global::forms.label for="url">{{ $tpl->__('label.ical_url') }}:</x-global::forms.label>
    <x-global::forms.text-input width="lg" id="url" name="url" autocomplete="off" value="{{ $values['url'] }}" /><br />

    <x-global::forms.label for="color">{{ $tpl->__('label.color') }}:</x-global::forms.label>
    <input type="text" name="colorClass" autocomplete="off" value="{{ $values['colorClass'] }}"  class="simpleColorPicker"/>

    @dispatchEvent('beforeSubmitButton')
    <br /><br />
    <x-global::forms.button tag="input" inputType="submit" name="save" id="save" :labelText="__('buttons.save')" />




    @dispatchEvent('beforeFormClose')

</form>

<script>
    leantime.ticketsController.initSimpleColorPicker();
</script>
