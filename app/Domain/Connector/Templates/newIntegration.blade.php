@extends($layout)

@section('content')

@php
    $provider = $provider ?? null;
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {!! __('headlines.integrations') !!} // {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <div class="maincontentinner">
        @include('connector::submodules.importProgress')
    </div>

    <div class="maincontentinner center">

        {!! $tpl->displayNotification() !!}

        <img width="200" src="{{ BASE_URL }}/{{ $provider->image }}" />
        <h5 class="subtitle">{{ __('headlines.new_integration') }}</h5>

        {{ $provider->name }}<br />
        {!! $provider->description !!}<br /><br />

        <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=connect" contentRole="primary">{{ __('buttons.click_to_connect') }}</x-global::forms.button>

    </div>
</div>

@once
@push('scripts')
<script type="text/javascript">
   jQuery(document).ready(function() {
   });
</script>
@endpush
@endonce

@endsection
