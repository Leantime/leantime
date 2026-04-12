@extends($layout)

@section('content')

@php
    $providerFields = $providerFields ?? [];
    $provider = $provider ?? null;
    $leantimeFields = $leantimeFields ?? [];
    $numberOfFields = $maxFields ?? 0;
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
                <h1>{!! __('headlines.submodules.importProgress') !!} // {{ $provider->name }}</h1>
            </div>
        </div>
    </div>
</div>

{!! $tpl->displayNotification() !!}

<div class="maincontent">
    <div class="maincontentinner">
        @include('connector::submodules.importProgress')
    </div>
    <div class="maincontentinner">
        <div class='center'>
            <div style='width:30%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_party_re_nmwj.svg') !!}
            </div>
            <br />
            <h3>Integration Success</h3>
            <p>Your data was synced successfully.</p>
            <br />
            <a class='btn btn-default' href='{{ BASE_URL }}/submodules.importProgress/show'>Go back to integrations</a>
        </div>
    </div>
</div>

@endsection
