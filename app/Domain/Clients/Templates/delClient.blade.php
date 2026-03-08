@php
    $client = $tpl->get('client');
@endphp

<x-globals::layout.page-header :icon="$tpl->getModulePicture()" subtitle="{{ __('label.administration') }}" headline="{{ sprintf(__('headline.delete_client'), $client['name']) }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <x-globals::elements.section-title variant="plain" icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>
        <div class="widgetcontent">

            <form method="post">
                @dispatchEvent('afterFormOpen')
                <p>{{ __('text.confirm_client_deletion') }}<br /></p>
                <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button link="/clients/showClient/{{ $client['id'] }}" type="primary">{{ __('buttons.back') }}</x-globals::forms.button>
                @dispatchEvent('beforeFormClose')
            </form>
        </div>

    </div>
</div>
