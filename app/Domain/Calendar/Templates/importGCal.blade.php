{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light"><x-global::elements.icon name="calendar_add_on" /> {{ __('label.import_ical') }}</h4>
{!!  __('label.import_ical_content')  !!}


<form action="{{ BASE_URL }}/calendar/importGCal" method="post" class="formModal">

    <label for="name">{{ $tpl->__('label.calendar_name') }}:</label>
    <x-globals::forms.input name="name" id="name" autocomplete="off" value="{{ $values['name'] }}" /><br />

    <label for="url">{{ $tpl->__('label.ical_url') }}:</label>
    <x-globals::forms.input name="url" id="url" autocomplete="off" style="width:300px;" value="{{ $values['url'] }}" /><br />

    <label for="color">{{ $tpl->__('label.color') }}:</label>
    <input type="text" name="colorClass" autocomplete="off" value="{{ $values['colorClass'] }}"  class="simpleColorPicker"/>

    @dispatchEvent('beforeSubmitButton')
    <br /><br />
    <x-globals::forms.button submit type="secondary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>




    @dispatchEvent('beforeFormClose')

</form>

<script>
    leantime.ticketsController.initSimpleColorPicker();
</script>
