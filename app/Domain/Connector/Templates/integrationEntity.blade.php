@php
    $providerEntities = $tpl->get('providerEntities');
    $provider = $tpl->get('provider');
    $leantimeEntities = $tpl->get('leantimeEntities');
    $integrationId = $tpl->get('integrationId');

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
        <div class="maincontentinner center">

        {!! $tpl->displayNotification() !!}

            <h5 class="subtitle">What are you importing?</h5>
            <br />
            On this screen you can choose what you would like to synchronize. Choose an entity on the left and map it to something in Leantime on the right.
            The arrow indicates that we will synchronize from one location to the other.<br /><br />

            <form method="post" action="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=fields{{ $urlAppend }}">

                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-2 right">
                        <h1>From (your integration)</h1>
                        <label for="providerEntities">{{ $provider->name }}</label>
                        <x-globals::forms.select name="providerEntities" id="providerEntities" style="width:100%;">
                            @foreach ($providerEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </div>
                    <div class="col-md-2" style="padding-top:50px;">
                        <i class="fa fa-arrow-right"></i>
                    </div>
                    <div class="col-md-2">
                        <h1>To (Leantime)</h1>

                        <label for="leantimeEntities">Leantime</label>
                        <x-globals::forms.select name="leantimeEntities" id="leantimeEntities" style="width:100%;">
                            @foreach ($leantimeEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </div>
                    <div class="col-md-3"></div>
                </div>

                <div class="left">
                    <x-globals::forms.button link="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}" type="secondary" class="pull-left">Back</x-globals::forms.button>
                </div>

                <div class="right">
                    <x-globals::forms.button submit type="secondary">Next</x-globals::forms.button>
                </div>
                <div class="clearall"></div>
            </form>
        </div>
    </div>
