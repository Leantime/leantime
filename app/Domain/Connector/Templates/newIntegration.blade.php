@php
    $provider = $tpl->get('provider');
@endphp

<x-globals::layout.page-header icon="hub" headline="{{ $tpl->__('headlines.integrations') }} // {{ $provider->name }}" />

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

        <x-globals::forms.button element="a" href="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=connect" contentRole="primary">Click Here to Connect</x-globals::forms.button>

    </div>
</div>

<script type="text/javascript">
   jQuery(document).ready(function() {
    });
</script>
