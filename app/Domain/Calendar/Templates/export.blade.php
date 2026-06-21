@extends($layout)

@section('content')

@php
    $url = $url ?? null;
@endphp

<h4 class="widgettitle title-light"><i class="fa fa-file-export"></i> {!! __('label.ical_export') !!}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/calendar/export">

    @dispatchEvent('afterFormOpen')

    {!! __('text.ical_export_description') !!}
    <br />

    @if ($url)
        {!! __('text.you_ical_url') !!}
        <br /><x-global::forms.text-input value='{{ $url }}' style='width:100%;' />
    @else
        {!! __('text.no_url') !!}
    @endif

    <div class="row">
        <div class="col-md-6">
            <input type="hidden" value="1" name="generateUrl" />

            @dispatchEvent('beforeSubmitButton')

            <br /><x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.generate_ical_url')" />

        </div>
        <div class="col-md-6 align-right">
            @if ($url)
                 <x-global::forms.button tag="a" link="{{ BASE_URL }}/calendar/export?remove=1" class="delete formModal" state="danger" variant="outline"><i class="fa fa-trash"></i> {!! __('links.remove_access') !!}</x-global::forms.button>
            @endif
        </div>
    </div>

    @dispatchEvent('beforeFormClose')

</form>

@endsection
