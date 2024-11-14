{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light"><i class="fa-regular fa-calendar-plus"></i> {{ __('label.edit_ical') }}</h4>
{!!  __('label.import_ical_content')  !!}


<form action="{{ BASE_URL }}/calendar/editExternal/{{ $values['id'] }}" method="post" class="formModal">

    <input type="hidden" name="save" value="1" />
    <label for="name">{{ $tpl->__('label.calendar_name') }}:</label>
    <input type="text" id="name" name="name" autocomplete="off" value="{{ $values['name'] }}" /><br />

    <label for="url">{{ $tpl->__('label.ical_url') }}:</label>
    <input type="text" id="url" name="url" autocomplete="off" style="width:300px;" value="{{ $values['url'] }}" /><br />

    <label for="color">{{ $tpl->__('label.color') }}:</label>
    <input type="text" name="colorClass" autocomplete="off" value="{{ $values['colorClass'] }}"  class="simpleColorPicker"/>

    @dispatchEvent('beforeSubmitButton')
    <br /><br />
    <input type="submit" name="save" id="save" value="{{ __('buttons.save') }}" class="btn" />




    @dispatchEvent('beforeFormClose')

</form>

<script>
    leantime.ticketsController.initSimpleColorPicker();
</script>
