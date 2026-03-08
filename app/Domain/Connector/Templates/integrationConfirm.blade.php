@php
    $providerFields = $tpl->get('providerFields');
    $provider = $tpl->get('provider');
    $leantimeFields = $tpl->get('leantimeFields');
    $numberOfFields = $tpl->get('maxFields');
    $urlAppend = '';
    if (isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = '&integrationId=' . $integrationId;
    }
@endphp

<x-globals::layout.page-header icon="hub" headline="{{ $tpl->__('headlines.connector') }} // {{ $provider->name }}" />

{!! $tpl->displayNotification() !!}

<div class="maincontent">
    <div class="maincontentinner">
        @php $tpl->displaySubmodule('connector-importProgress') @endphp
    </div>
    <div class="maincontentinner">
        <div class="center">
            <div class="tw:w-1/3 tw:mx-auto svgContainer">
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_party_re_nmwj.svg') !!}
            </div>
            <br />
            <h3>Integration Success</h3>
            <p>Your data was synced successfully.</p>
            <br />
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/connector/show" contentRole="secondary">Go back to integrations</x-globals::forms.button>
        </div>
    </div>
</div>
