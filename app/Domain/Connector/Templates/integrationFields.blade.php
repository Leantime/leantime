@extends($layout)

@section('content')

@php
    $providerFields = $providerFields ?? [];
    $provider = $provider ?? null;
    $leantimeFields = $leantimeFields ?? [];
    $flags = $flags ?? [];
    $importProjects = $importProjects ?? [];
    $urlAppend = '';

    if (isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = '&integrationId=' . $integrationId;
    }

    // A column is matched to a field only when its heading is the field's name (or a former name),
    // ignoring case. Id never is: a matched Id updates existing items, so that is always a choice.
    $matchesField = function (string $heading, string $key, array $field): bool {
        if (in_array($key, ['id', 'itemId'], true)) {
            return false;
        }
        foreach (array_merge([$field['name']], $field['aliases'] ?? []) as $name) {
            if (strcasecmp(trim($heading), $name) === 0) {
                return true;
            }
        }

        return false;
    };
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {!! __('headlines.connector') !!} // {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress')
    </x-global::layout.content-box>

    <x-global::layout.content-box id="import-fields">
        {!! $tpl->displayNotification() !!}
        <x-global::elements.section-header as="h5">{{ __('headlines.integration_match_fields') }}</x-global::elements.section-header>
        <p>{{ __('text.integration_match_fields') }}</p>

        <form method="post" action="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=parse{{ $urlAppend }}">

            @if (!empty($importProjects))
                <x-global::forms.field layout="inline" for="importProjectId" :label="__('label.import_into_project')" :help="__('text.import_into_project')">
                    <x-global::forms.select name="importProjectId" id="importProjectId" width="md" enhanced aria-describedby="importProjectId-help">
                        <option value="">{{ __('label.import_choose_project') }}</option>
                        @foreach ($importProjects as $project)
                            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                        @endforeach
                    </x-global::forms.select>
                </x-global::forms.field>
            @endif

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>{{ __('label.source_field') }}</th>
                    <th>{{ __('label.leantime_field') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($providerFields as $heading)
                    <tr>
                        <td>{{ $heading }}</td>
                        <td>
                            {{-- "Don't import" comes first, so a column that matches no field stays out of the import. --}}
                            <x-global::forms.select name="field_{{ md5($heading) }}" width="md" :aria-label="$heading">
                                <option value="">{{ __('label.dont_map') }}</option>
                                @foreach ($leantimeFields as $key => $field)
                                    <option value="{{ $heading }}|{{ $key }}" @if ($matchesField((string) $heading, (string) $key, $field)) selected @endif>{{ $field['name'] }}</option>
                                @endforeach
                            </x-global::forms.select>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="left">
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration/?provider={{ $provider->id }}&step=entity{{ $urlAppend }}" contentRole="tertiary">{{ __('buttons.back') }}</x-global::forms.button>
            </div>
            <div class="right">
                <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.next') }}</x-global::forms.button>
            </div>
            <div class="clearall"></div>
        </form>

        <x-slot:right>
            <x-global::elements.section-header as="h5">{{ __('headlines.integration_requirements') }}</x-global::elements.section-header>
            <p>{{ __('text.integration_requirements') }}</p>
            <ul>
                @foreach ($flags as $flag)
                    <li>{{ $flag }}</li>
                @endforeach
            </ul>
        </x-slot:right>
    </x-global::layout.content-box>
</div>

@endsection
