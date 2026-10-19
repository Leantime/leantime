@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {{ __('headlines.integrations') }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}
        <x-global::elements.section-header as="h5">{{ __('headlines.integrations_sync') }}</x-global::elements.section-header>
        <p>{{ __('label.available_integrations') }}</p>

        <div class="row">
            @foreach ($providers as $provider)
                <div class="col-md-3">
                    <div class="profileBox">
                        <div class="commentImage gradient">
                            <img src="{{ BASE_URL }}/{{ $provider->image }}"/>
                        </div>
                        <span class="userName">
                            <strong>{{ $provider->name }}</strong>
                            <br /><small>{{ sprintf(__('label.available_methods'), implode(', ', $provider->methods)) }}</small>
                            <br /><br />
                            {!! $provider->description !!}
                        </span>
                        <br />

                        @if (isset($provider->button))
                            <x-global::forms.button tag="a" link="{{ $provider->button['url'] }}" contentRole="primary">{{ $provider->button['text'] }}</x-global::forms.button>
                        @else
                            <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}" contentRole="primary">{{ __('buttons.create_new_integration') }}</x-global::forms.button>
                        @endif

                        <div class="clearall"></div>
                    </div>
                </div>
            @endforeach
        </div>

    </x-global::layout.content-box>

    {{-- Existing Integrations section placeholder --}}

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
