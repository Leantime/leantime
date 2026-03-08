@php
    $providerFields = $tpl->get('providerFields');
    $provider = $tpl->get('provider');
    $leantimeFields = $tpl->get('leantimeFields');
    $numberOfFields = $tpl->get('maxFields');
    $values = $tpl->get('values');
    $flags = $tpl->get('flags');
    $fields = $tpl->get('fields');
    $urlAppend = '';
    if (isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = '&integrationId=' . $integrationId;
    }
@endphp

<x-globals::layout.page-header icon="hub" headline="{{ $tpl->__('headlines.integrations') }} // {{ $provider->name }}" />

<div class="maincontent">
    <div class="maincontentinner">
        @php $tpl->displaySubmodule('connector-importProgress') @endphp
    </div>
    <div class="maincontentinner center">
        {!! $tpl->displayNotification() !!}

        <h5 class="subtitle">Review</h5>

        @if (! empty($flags))
            <p class="tw:italic">Please resolve the following errors and reconnect your integration:</p>
            <ul class="tw:pl-5 tw:mb-5">
                @php $messages = []; @endphp
                @foreach ($flags as $flag)
                    @if (in_array($flag, $messages) === false)
                        <li class="tw:mr-2 tw:text-red-600 tw:italic">{{ $flag }}</li>
                        @php $messages[] = $flag; @endphp
                    @endif
                @endforeach
            </ul>
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=fields{{ $urlAppend }}" contentRole="primary" class="pull-left">Go Back</x-globals::forms.button>
        @else
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=import" contentRole="primary" class="right">Confirm</x-globals::forms.button>
        @endif
        <div class="clearall"></div>

        <p>All set, we are importing the data you see below.</p>
        <br />

        <x-globals::elements.table class="tw:w-full">
            <x-slot:head>
                <tr>
                    @foreach ($fields as $sourceField => $leantimeField)
                        <th>{{ $leantimeField['leantimeField'] }}</th>
                    @endforeach
                </tr>
            </x-slot:head>
            @foreach ($values as $record)
                <tr>
                    @foreach ($record as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </x-globals::elements.table>
        <br />

        <div class="clearall"></div>

    </div>
</div>
