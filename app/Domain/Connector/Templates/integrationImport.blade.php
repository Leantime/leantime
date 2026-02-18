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

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <h1>{{ $tpl->__('headlines.integrations') }} // {{ $provider->name }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        @php $tpl->displaySubmodule('connector-importProgress') @endphp
    </div>
    <div class="maincontentinner tw:text-center">
        {!! $tpl->displayNotification() !!}

        <h5 class="subtitle">Review</h5>

        @if (! empty($flags))
            <p style="font-style: oblique">Please resolve the following errors and reconnect your integration:</p>
            <ul style="padding-left: 20px; margin-bottom: 20px;">
                @php $messages = []; @endphp
                @foreach ($flags as $flag)
                    @if (in_array($flag, $messages) === false)
                        <li style="margin-right: 10px; color: red; font-style: oblique;">{{ $flag }}</li>
                        @php $messages[] = $flag; @endphp
                    @endif
                @endforeach
            </ul>
            <x-global::button link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=fields{{ $urlAppend }}" type="primary" class="tw:float-left">Go Back</x-global::button>
        @else
            <x-global::button link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=import" type="primary" class="right">Confirm</x-global::button>
        @endif
        <div class="clearall"></div>

        <p>All set, we are importing the data you see below.</p>
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

    </div>
</div>
