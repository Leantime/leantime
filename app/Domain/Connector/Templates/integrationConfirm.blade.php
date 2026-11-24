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
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

{!! $tpl->displayNotification() !!}

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress', ['currentStep' => 'import', 'allStepsDone' => $importResult === null || $importResult->isSuccessful()])

        @if ($importResult === null || $importResult->isSuccessful())
            <x-global::elements.empty-state image="undraw_party_re_nmwj.svg" titleAs="h3" :title="__('headlines.integration_success')">
                @if ($importResult !== null)
                    {{ sprintf(__('text.integration_imported_count'), $imported) }}
                @else
                    {{ __('text.integration_synced') }}
                @endif
                <x-slot:actions>
                    <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/show" contentRole="secondary">{{ __('links.back_to_integrations') }}</x-global::forms.button>
                </x-slot:actions>
            </x-global::elements.empty-state>
        @else
            {{-- Some rows came in (warning) or none did (danger); each failed row with its sheet row number. --}}
            <x-global::feedback.alert
                :tone="$imported > 0 ? 'warning' : 'danger'"
                :title="$imported > 0 ? __('headlines.integration_partial') : __('headlines.integration_failed')"
            >
                <p>{{ $imported > 0 ? sprintf(__('text.integration_partial'), $imported, $failed) : __('text.integration_failed') }}</p>
                <ul>
                    @foreach ($importResult->failures as $failure)
                        <li>{{ sprintf(__('notification.import_row_failed'), $failure['row'], $failure['message']) }}</li>
                    @endforeach
                </ul>
            </x-global::feedback.alert>

            <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}" contentRole="secondary">{{ __('links.integration_import_again') }}</x-global::forms.button>
            <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/show" contentRole="tertiary">{{ __('links.back_to_integrations') }}</x-global::forms.button>
        @endif
    </x-global::layout.content-box>
</div>

@endsection
