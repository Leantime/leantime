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
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

    <div class="maincontent">
        <x-global::layout.content-box>
        @include('connector::submodules.importProgress', ['currentStep' => 'entity'])


        {!! $tpl->displayNotification() !!}

            <x-global::elements.section-header as="h5">{{ __('headlines.integration_what_importing') }}</x-global::elements.section-header>

            <form method="post" action="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=fields{{ $urlAppend }}">

                @if (count($providerEntities) === 1)
                    {{-- One source (a file is one sheet): only ask what its rows are. --}}
                    <input type="hidden" name="providerEntities" value="{{ array_key_first($providerEntities) }}" />
                    <x-global::forms.field layout="inline" for="leantimeEntities" :label="__('label.import_entity')">
                        <x-global::forms.select width="md" name="leantimeEntities" id="leantimeEntities">
                            @foreach ($leantimeEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>
                @else
                    <p>{{ __('text.integration_choose_entity') }}</p>
                    <x-global::forms.field layout="inline" for="providerEntities" :label="$provider->name">
                        <x-global::forms.select width="md" name="providerEntities" id="providerEntities">
                            @foreach ($providerEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>
                    <x-global::forms.field layout="inline" for="leantimeEntities" label="Leantime">
                        <x-global::forms.select width="md" name="leantimeEntities" id="leantimeEntities">
                            @foreach ($leantimeEntities as $key => $entity)
                                <option value="{{ $key }}">{{ $entity['name'] }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>
                @endif

                <div class="left">
                    <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}" contentRole="tertiary">{{ __('buttons.back') }}</x-global::forms.button>
                </div>

                <div class="right">
                    <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.next') }}</x-global::forms.button>
                </div>
                <div class="clearall"></div>
            </form>
        </x-global::layout.content-box>
    </div>

@endsection
