@extends($layout)
@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{!! __('label.administration') !!}</h5>
        <h1>{!! sprintf(__('headline.delete_client'), $client['name']) !!}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h4 class="widget widgettitle">{!! __('subtitles.delete') !!}</h4>
        <div class="widgetcontent">

            <form method="post">

                @dispatchEvent('afterFormOpen')

                <p>{!! __('text.confirm_client_deletion') !!}<br /></p>
                <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
                <x-global::forms.button tag="a" link="/clients/showClient/{{ $client['id'] }}" contentRole="tertiary">{!! __('buttons.back') !!}</x-global::forms.button>

                @dispatchEvent('beforeFormClose')

            </form>
        </div>

    </div>
</div>

@endsection
