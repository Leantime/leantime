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
    {!! __('headlines.integrations') !!} // {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress')
    </x-global::layout.content-box>
    <x-global::layout.content-box class="center">
        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h5">{{ __('headlines.integration_review') }}</x-global::elements.section-header>

        @if (!empty($flags))
            <p style="font-style: oblique">{{ __('text.integration_resolve_errors') }}</p>
            <ul style="padding-left: 20px; margin-bottom: 20px;">
                @php $messages = []; @endphp
                @foreach ($flags as $flag)
                    @if (!in_array($flag, $messages))
                        <li style="margin-right: 10px; color: red; font-style: oblique;">{{ $flag }}</li>
                        @php $messages[] = $flag; @endphp
                    @endif
                @endforeach
            </ul>
            <x-global::forms.button tag="a" class="pull-left" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=fields{{ $urlAppend }}" contentRole="tertiary">{{ __('links.go_back') }}</x-global::forms.button>
        @else
            <x-global::forms.button tag="a" class="right" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=import" contentRole="primary">{{ __('buttons.confirm') }}</x-global::forms.button>
        @endif
        <div class="clearall"></div>

        <p>{{ __('text.integration_importing') }}</p>
        <br />

        <table width="100%">
            <thead>
            <tr>
                @foreach ($fields as $sourceField => $leantimeField)
                    <th>{{ $leantimeField['leantimeField'] }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($values as $record)
                <tr>
                    @foreach ($record as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <br />

        <div class="clearall"></div>

    </x-global::layout.content-box>
</div>

@endsection
