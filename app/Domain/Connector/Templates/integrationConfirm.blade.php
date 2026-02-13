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

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1>{{ $tpl->__('headlines.connector') }} // {{ $provider->name }}</h1>
            </div>
        </div>
    </div>
</div>

{!! $tpl->displayNotification() !!}

<div class="maincontent">
    <div class="maincontentinner">
        @php $tpl->displaySubmodule('connector-importProgress') @endphp
    </div>
    <div class="maincontentinner">
        <div class="center">
            <div style="width:30%" class="svgContainer">
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_party_re_nmwj.svg') !!}
            </div>
            <br />
            <h3>Integration Success</h3>
            <p>Your data was synced successfully.</p>
            <br />
            <a class="btn btn-default" href="{{ BASE_URL }}/connector/show">Go back to integrations</a>
        </div>
    </div>
</div>
