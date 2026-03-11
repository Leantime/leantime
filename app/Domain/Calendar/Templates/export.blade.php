@php
    $url = $tpl->get('url');
@endphp

<x-globals::elements.section-title icon="upload_file">{{ __('label.ical_export') }}</x-globals::elements.section-title>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/calendar/export">

    @dispatchEvent('afterFormOpen')

    {{ __('text.ical_export_description') }}
    <br />

    @if($url)
        {{ __('text.you_ical_url') }}
        <br /><x-globals::forms.text-input name="ical_url" value="{{ $url }}" readonly class="tw:w-full" />
    @else
        {{ __('text.no_url') }}
    @endif

    <div class="row">
        <div class="col-md-6">
            <input type="hidden" value="1" name="generateUrl" />

            @dispatchEvent('beforeSubmitButton')

            <br /><x-globals::forms.button :submit="true" contentRole="primary">{{ __('buttons.generate_ical_url') }}</x-globals::forms.button>

        </div>
        <div class="col-md-6 align-right">
            @if($url)
                <a href="{{ BASE_URL }}/calendar/export?remove=1" class="delete formModal"><x-globals::elements.icon name="delete" /> {{ __('links.remove_access') }}</a>
            @endif
        </div>
    </div>

    @dispatchEvent('beforeFormClose')

</form>
