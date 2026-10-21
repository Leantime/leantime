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

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {!! __('headlines.connector') !!} // {{ $provider->name }}
</x-global::layout.page-header>

{!! $tpl->displayNotification() !!}

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress')
    </x-global::layout.content-box>
    <x-global::layout.content-box>
        <div class='center'>
            <div style='width:30%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_party_re_nmwj.svg') !!}
            </div>
            <br />
            <h3>{{ __('headlines.integration_success') }}</h3>
            <p>{{ __('text.integration_synced') }}</p>
            <br />
            <a class='btn btn-default' href='{{ BASE_URL }}/connector/show'>{{ __('links.back_to_integrations') }}</a>
        </div>
    </x-global::layout.content-box>
</div>

@endsection
