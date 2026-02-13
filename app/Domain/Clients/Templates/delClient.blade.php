@php
    $client = $tpl->get('client');
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ sprintf(__('headline.delete_client'), $client['name']) }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h4 class="widget widgettitle">{{ __('subtitles.delete') }}</h4>
        <div class="widgetcontent">

            <form method="post">
                @dispatchEvent('afterFormOpen')
                <p>{{ __('text.confirm_client_deletion') }}<br /></p>
                <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
                <a class="btn btn-primary" href="/clients/showClient/{{ $client['id'] }}">{{ __('buttons.back') }}</a>
                @dispatchEvent('beforeFormClose')
            </form>
        </div>

    </div>
</div>
