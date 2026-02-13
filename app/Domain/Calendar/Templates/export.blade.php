@php
    $url = $tpl->get('url');
@endphp

<h4 class="widgettitle title-light"><i class="fa fa-file-export"></i> {{ __('label.ical_export') }}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/calendar/export">

    @dispatchEvent('afterFormOpen')

    {{ __('text.ical_export_description') }}
    <br />

    @if($url)
        {{ __('text.you_ical_url') }}
        <br /><input type="text" value="{{ $url }}" style="width:100%;" />
    @else
        {{ __('text.no_url') }}
    @endif

    <div class="row">
        <div class="col-md-6">
            <input type="hidden" value="1" name="generateUrl" />

            @dispatchEvent('beforeSubmitButton')

            <br /><input type="submit" value="{{ __('buttons.generate_ical_url') }}" />

        </div>
        <div class="col-md-6 align-right">
            @if($url)
                <a href="{{ BASE_URL }}/calendar/export?remove=1" class="delete formModal"><i class="fa fa-trash"></i> {{ __('links.remove_access') }}</a>
            @endif
        </div>
    </div>

    @dispatchEvent('beforeFormClose')

</form>
