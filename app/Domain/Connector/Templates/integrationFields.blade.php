@php
    $providerFields = $tpl->get('providerFields');
    $provider = $tpl->get('provider');
    $leantimeFields = $tpl->get('leantimeFields');
    $numberOfFields = $tpl->get('maxFields');
    $flags = $tpl->get('flags');
    $urlAppend = '';

    if (isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = '&integrationId=' . $integrationId;
    }
@endphp

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <h1>{{ $tpl->__('headlines.connector') }} // {{ $provider->name }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        @php $tpl->displaySubmodule('connector-importProgress') @endphp
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="maincontentinner center">

                {!! $tpl->displayNotification() !!}
                <h5 class="subtitle">Match Fields</h5>
                <p class="mb-2">Match the fields from your source to the corresponding fields in Leantime</p><br />

                <form method="post" action="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=parse{{ $urlAppend }}">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="center">Source Field</th>
                            <th class="center">Leantime Field</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($providerFields as $key => $entity)
                            <tr>
                                <td class="center">{{ $entity }}</td>
                                <td class="center">
                                    <x-globals::forms.select name="field_{{ md5($entity) }}">
                                        @foreach ($leantimeFields as $key2 => $fields)
                                            <option value="{{ $entity }}|{{ $key2 }}" {{ ($entity == $fields['name'] && ! in_array($key2, ['id', 'itemId'])) ? "selected='selected'" : '' }}>
                                                {{ $fields['name'] }}
                                            </option>
                                        @endforeach
                                        <option value="">Don't map</option>
                                    </x-globals::forms.select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="left">
                        <x-globals::forms.button link="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}" type="secondary" class="pull-left">Back</x-globals::forms.button>
                    </div>
                    <div class="right">
                        <x-globals::forms.button submit type="primary">Next</x-globals::forms.button>
                    </div>
                    <div class="clearall"></div>
                </form>
            </div>
        </div>
        <div class="col-md-3">
            <div class="maincontentinner">
            <h5 class="subtitle">Requirements for a successful import</h5>
            <p>Please review these requirements and make sure your import and mapping covers everything.</p>
            @foreach ($flags as $flag)
                <hr />
                <p style="padding-left:10px"><strong>{{ $flag }}</strong></p>
            @endforeach
            </div>
        </div>

    </div>

</div>
