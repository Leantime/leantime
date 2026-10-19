@extends($layout)

@section('content')

@php
    $provider = $provider ?? null;
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

        <img width="200" src="{{ BASE_URL }}/{{ $provider->image }}" />
        <x-global::elements.section-header as="h5">{{ __('headlines.new_integration') }}</x-global::elements.section-header>

        {{ $provider->name }}<br />
        {!! $provider->description !!}<br /><br />

        <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=connect" contentRole="primary">{{ __('buttons.click_to_connect') }}</x-global::forms.button>

    </x-global::layout.content-box>
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
