@extends($layout)

@section('content')

@php
    $providerFields = $providerFields ?? [];
    $provider = $provider ?? null;
    $leantimeFields = $leantimeFields ?? [];
    $numberOfFields = $maxFields ?? 0;
    $flags = $flags ?? [];
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
    <div class="row">
        <div class="col-md-9">
            <x-global::layout.content-box class="center">

                {!! $tpl->displayNotification() !!}
                <x-global::elements.section-header as="h5">{{ __('headlines.integration_match_fields') }}</x-global::elements.section-header>
                <p class="mb-2">{{ __('text.integration_match_fields') }}</p><br />

                <form method="post" action="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=parse{{ $urlAppend }}">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="center">{{ __('label.source_field') }}</th>
                            <th class="center">{{ __('label.leantime_field') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($providerFields as $key => $entity)
                            <tr>
                                <td class="center">{{ $entity }}</td>
                                <td class="center">
                                    <x-global::forms.select class="form-control" name="field_{{ md5($entity) }}">
                                        @foreach ($leantimeFields as $key2 => $fields)
                                            <option value="{{ $entity }}|{{ $key2 }}" {{ ($entity == $fields['name'] && !in_array($key2, ['id', 'itemId'])) ? "selected='selected'" : '' }}>
                                                {{ $fields['name'] }}
                                            </option>
                                        @endforeach
                                        <option value="">{{ __('label.dont_map') }}</option>
                                    </x-global::forms.select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="left">
                        <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}" contentRole="tertiary" class="pull-left">{{ __('buttons.back') }}</x-global::forms.button>
                    </div>
                    <div class="right">
                        <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.next') }}</x-global::forms.button>
                    </div>
                    <div class="clearall"></div>
                </form>
            </x-global::layout.content-box>
        </div>
        <div class="col-md-3">
            <x-global::layout.content-box>
            <x-global::elements.section-header as="h5">{{ __('headlines.integration_requirements') }}</x-global::elements.section-header>
            <p>{{ __('text.integration_requirements') }}</p>
            @foreach ($flags as $flag)
                <hr />
                <p style="padding-left:10px"><strong>{{ $flag }}</strong></p>
            @endforeach
            </x-global::layout.content-box>
        </div>

    </div>

</div>

@endsection
