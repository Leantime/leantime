<x-global::content.modal.modal-buttons/>

@displayNotification()

<h4 class="widgettitle title-light"><i class="fa-regular fa-calendar-plus"></i> {{ __('label.import_ical') }}</h4>
{!!  __('label.import_ical_content')  !!}


<x-global::content.modal.form action="{{ BASE_URL }}/calendar/importGCal">

    <label for="name">{{ $tpl->__('label.calendar_name') }}:</label>
    <input type="text" id="name" name="name" autocomplete="off" value="{{ $values['name'] }}" /><br />

    <label for="url">{{ $tpl->__('label.ical_url') }}:</label>
    <input type="text" id="url" name="url" autocomplete="off" style="width:300px;" value="{{ $values['url'] }}" /><br />

    <label for="color">{{ $tpl->__('label.color') }}:</label>
    <input type="text" name="colorClass" autocomplete="off" value="{{ $values['colorClass'] }}"  class="simpleColorPicker"/>

    @dispatchEvent('beforeSubmitButton')
    <br /><br />
    <x-global::forms.button  type="submit" name="save" id="save" class="btn">
        {{ __('buttons.save') }}
    </x-global::forms.button>




    @dispatchEvent('beforeFormClose')

</x-global::content.modal.form>

<script>
    leantime.ticketsController.initSimpleColorPicker();
</script>
