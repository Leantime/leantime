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
                <x-global::button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-global::button>
                <x-global::button link="/clients/showClient/{{ $client['id'] }}" type="primary">{{ __('buttons.back') }}</x-global::button>
                @dispatchEvent('beforeFormClose')
            </form>
        </div>

    </div>
</div>
