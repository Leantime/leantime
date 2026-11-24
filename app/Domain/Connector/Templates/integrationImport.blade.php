@extends($layout)

@section('content')

@php
    $providerFields = $providerFields ?? [];
    $provider = $provider ?? null;
    $leantimeFields = $leantimeFields ?? [];
    $numberOfFields = $maxFields ?? 0;
    $values = $values ?? [];
    $flags = $flags ?? [];
    $fields = $fields ?? [];
    $urlAppend = '';
    if (isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = '&integrationId=' . $integrationId;
    }
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {!! __('headlines.connector') !!} // {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress')
    </x-global::layout.content-box>
    <x-global::layout.content-box>
        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h5">{{ __('headlines.integration_review') }}</x-global::elements.section-header>

        @if (!empty($flags))
            <x-global::feedback.alert tone="danger" :title="__('text.integration_resolve_errors')">
                <ul>
                    @foreach (array_unique($flags) as $flag)
                        <li>{{ $flag }}</li>
                    @endforeach
                </ul>
            </x-global::feedback.alert>
            <x-global::forms.button tag="a" class="pull-left" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=fields{{ $urlAppend }}" contentRole="tertiary">{{ __('links.go_back') }}</x-global::forms.button>
        @else
            <form method="post" action="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=import{{ $urlAppend }}" class="right">
                {{-- Ties Confirm to this review: a Confirm from an older review imports nothing. --}}
                <input type="hidden" name="importToken" value="{{ $importToken ?? '' }}" />
                <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.import_rows') }}</x-global::forms.button>
            </form>
        @endif
        <div class="clearall"></div>

        @if (empty($flags))
            <p>{{ __('text.integration_importing') }}</p>
        @endif

        {{-- One cell per mapped column, under its Leantime field; the row number is the spreadsheet's. --}}
        <div class="tw-overflow-x-auto tw-mb-4">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>{{ __('label.import_row') }}</th>
                @foreach ($fields as $field)
                    <th>{{ $leantimeFields[$field['leantimeField']]['name'] ?? $field['leantimeField'] }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($values as $index => $record)
                <tr>
                    <td>{{ $record[\Leantime\Domain\Connector\Services\Connector::ROW_KEY] ?? ((int) $index + 2) }}</td>
                    @foreach ($fields as $field)
                        <td>{{ is_scalar($record[$field['sourceField']] ?? null) ? $record[$field['sourceField']] : '' }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div class="clearall"></div>

    </x-global::layout.content-box>
</div>

@endsection
