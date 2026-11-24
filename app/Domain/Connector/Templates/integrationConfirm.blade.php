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

    /** @var \Leantime\Domain\Connector\Models\ImportResult|null $importResult */
    $importResult = $importResult ?? null;
    $imported = $importResult?->imported ?? 0;
    $failed = $importResult?->failedCount() ?? 0;
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
        @if ($importResult === null || $importResult->isSuccessful())
            <div class='center'>
                <div style='width:30%' class='svgContainer'>
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_party_re_nmwj.svg') !!}
                </div>
                <br />
                <h3>{{ __('headlines.integration_success') }}</h3>
                <p>{{ __('text.integration_synced') }}</p>
                @if ($importResult !== null)
                    <p>{{ sprintf(__('text.integration_imported_count'), $imported) }}</p>
                @endif
                <br />
                <a class='btn btn-default' href='{{ BASE_URL }}/connector/show'>{{ __('links.back_to_integrations') }}</a>
            </div>
        @else
            @if ($imported > 0)
                <h3>{{ __('headlines.integration_partial') }}</h3>
                <p>{{ sprintf(__('text.integration_partial'), $imported, $failed) }}</p>
            @else
                <h3>{{ __('headlines.integration_failed') }}</h3>
                <p>{{ __('text.integration_failed') }}</p>
            @endif
            <ul style="padding-left: 20px; margin: 15px 0;">
                @foreach ($importResult->failures as $failure)
                    <li style="color: red;">{{ sprintf(__('notification.import_row_failed'), $failure['row'], $failure['message']) }}</li>
                @endforeach
            </ul>
            <a class='btn btn-default' href='{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}'>{{ __('links.integration_import_again') }}</a>
            <a class='btn btn-link' href='{{ BASE_URL }}/connector/show'>{{ __('links.back_to_integrations') }}</a>
        @endif
    </x-global::layout.content-box>
</div>

@endsection
