@php
    $provider = $tpl->get('provider');
@endphp

<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon name="hub" /></div>
    <div class="pagetitle">
        <h1>{{ $tpl->__('headlines.integrations') }} // {{ $provider->name }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        @php $tpl->displaySubmodule('connector-importProgress') @endphp
    </div>

    <div class="maincontentinner center">

        {!! $tpl->displayNotification() !!}

        <img width="200" src="{{ BASE_URL }}/{{ $provider->image }}" />
        <h5 class="subtitle">New Integration</h5>

        {{ $provider->name }}<br />
        {!! $provider->description !!}<br /><br />

        <x-globals::forms.button link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=connect" type="primary">Click Here to Connect</x-globals::forms.button>

    </div>
</div>

<script type="text/javascript">
   jQuery(document).ready(function() {
    });
</script>
