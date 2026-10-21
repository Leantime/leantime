@extends($layout)

@section('content')

@php
    $providerEntities = $providerEntities ?? [];
    $provider = $provider ?? null;
    $leantimeEntities = $leantimeEntities ?? [];
    $integrationId = $integrationId ?? null;

    $urlAppend = '';
    if (isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = '&integrationId=' . $integrationId;
    }
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {!! __('headlines.connector') !!} // {{ $provider->name }}
</x-global::layout.page-header>

    <div class="maincontent">
        <x-global::layout.content-box>
            @include('connector::submodules.importProgress')
        </x-global::layout.content-box>
        <x-global::layout.content-box class="center">

        {!! $tpl->displayNotification() !!}

            <x-global::elements.section-header as="h5">{{ __('headlines.integration_what_importing') }}</x-global::elements.section-header>
            <br />
            {{ __('text.integration_choose_entity') }}
            {{ __('text.integration_arrow_direction') }}<br /><br />

            <form method="post" action="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=fields{{ $urlAppend }}">

                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-2 right">
                        <h1>{{ __('headlines.integration_from') }}</h1>
                        <x-global::forms.label for="providerEntities">{{ $provider->name }}</x-global::forms.label>
                        <x-global::forms.select width="full" name="providerEntities" id="providerEntities">
                            @foreach ($providerEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </div>
                    <div class="col-md-2" style="padding-top:50px;">
                        <i class="fa fa-arrow-right"></i>
                    </div>
                    <div class="col-md-2">
                        <h1>{{ __('headlines.integration_to') }}</h1>

                        <x-global::forms.label for="leantimeEntities">Leantime</x-global::forms.label>
                        <x-global::forms.select width="full" name="leantimeEntities" id="leantimeEntities">
                            @foreach ($leantimeEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </div>
                    <div class="col-md-3"></div>
                </div>

                <div class="left">
                    <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}" contentRole="tertiary" class="pull-left">{{ __('buttons.back') }}</x-global::forms.button>
                </div>

                <div class="right">
                    <x-global::forms.button tag="input" inputType="submit" :labelText="__('buttons.next')" />
                </div>
                <div class="clearall"></div>
            </form>
        </x-global::layout.content-box>
    </div>

@endsection
