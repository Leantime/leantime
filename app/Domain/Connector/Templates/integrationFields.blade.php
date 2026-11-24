@extends($layout)

@section('content')

@php
    $providerFields = $providerFields ?? [];
    $provider = $provider ?? null;
    $leantimeFields = $leantimeFields ?? [];
    $guide = $guide ?? [];
    $examples = $examples ?? [];
    $entity = $entity ?? '';
    $importProjects = $importProjects ?? [];
    $integrationId = isset($integrationId) && is_numeric($integrationId) ? (int) $integrationId : null;

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

    // Field matched to each column on load, and the columns matched to each field.
    $matchedField = [];
    $columnsOf = [];
    foreach ($providerFields as $heading) {
        foreach ($leantimeFields as $key => $field) {
            if ($matchesField((string) $heading, (string) $key, $field)) {
                $matchedField[$heading] = $key;
                $columnsOf[$key][] = $heading;
                break;
            }
        }
    }
    $matchedCount = count($matchedField);
    $columnCount = count($providerFields);
    $countText = match (true) {
        $columnCount > 0 && $matchedCount === $columnCount => sprintf(__('text.import_match_all'), $columnCount),
        $matchedCount > 0 => sprintf(__('text.import_match_some'), $matchedCount, $columnCount),
        default => __('text.import_match_none'),
    };

    $previousStep = $provider->previousStep('fields');
    $backUrl = $previousStep !== null ? $provider->stepUrl($previousStep, $integrationId) : BASE_URL.'/connector/show';
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box id="import-fields">
        @include('connector::submodules.importProgress', ['currentStep' => 'fields'])

        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h4">{{ __('headlines.integration_match_fields') }}</x-global::elements.section-header>
        <p class="lt-lead">{{ __('text.integration_match_fields') }} {{ $countText }}</p>

        <form id="import-fields-form" method="post" action="{{ $provider->stepUrl('parse', $integrationId) }}">

            @if (!empty($importProjects))
                <div class="lt-import-question">
                {{-- Nothing is pre-selected. The help says why the question is asked; it changes when a column is matched to Project. --}}
                <x-global::forms.field for="importProjectId" :label="__('label.import_project_for_'.$entity)">
                    <x-global::forms.select name="importProjectId" id="importProjectId" width="lg" enhanced aria-describedby="importProjectId-help">
                        <option value="">{{ __('label.import_choose_project') }}</option>
                        @foreach ($importProjects as $project)
                            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                        @endforeach
                    </x-global::forms.select>
                    <x-slot:help>
                        <span data-project-help="none" @if (isset($columnsOf['projectName'])) hidden @endif>{{ __('text.import_project_help_none') }}</span>
                        <span data-project-help="column" @if (! isset($columnsOf['projectName'])) hidden @endif>{{ __('text.import_project_help_column') }}</span>
                    </x-slot:help>
                </x-global::forms.field>
                </div>
            @endif

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>{{ __('label.source_field') }}</th>
                    <th>{{ __('label.import_first_value') }}</th>
                    <th>{{ __('label.leantime_field') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($providerFields as $heading)
                    <tr>
                        <td><strong>{{ $heading }}</strong></td>
                        <td class="lt-cell-note">
                            @if (($examples[$heading] ?? '') !== '')
                                {{ \Illuminate\Support\Str::limit($examples[$heading], 60) }}
                            @else
                                {{ __('text.import_column_empty') }}
                            @endif
                        </td>
                        <td>
                            {{-- "Don't import" comes first, so a column that matches no field stays out of the import. --}}
                            <x-global::forms.select name="field_{{ md5($heading) }}" width="md" :aria-label="$heading" data-import-column>
                                <option value="">{{ __('label.dont_map') }}</option>
                                @foreach ($leantimeFields as $key => $field)
                                    <option value="{{ $heading }}|{{ $key }}" @if (($matchedField[$heading] ?? null) === $key) selected @endif>{{ $field['name'] }}</option>
                                @endforeach
                            </x-global::forms.select>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="lt-form-actions">
                <x-global::forms.button tag="a" :link="$backUrl" contentRole="tertiary">{{ __('buttons.back') }}</x-global::forms.button>
                <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.import_next_check') }}</x-global::forms.button>
            </div>
        </form>

        <x-slot:right>
            <x-global::elements.section-header as="h5">{{ __('headlines.import_what_leantime_needs') }}</x-global::elements.section-header>
            {{-- Each item says where its value comes from: a column of the file, the project chosen above, or nothing yet. --}}
            <ul class="lt-checklist" id="import-checklist"
                data-text-column="{{ __('text.import_guide_from_column') }}"
                data-text-columns="{{ __('text.import_guide_from_columns') }}"
                data-text-picker="{{ __('text.import_guide_from_picker') }}"
                data-text-missing="{{ __('text.import_guide_missing') }}">
                @foreach ($guide as $item)
                    @php
                        $columns = array_merge(...array_map(fn (string $field) => $columnsOf[$field] ?? [], $item['fields']));
                        $state = $columns !== [] ? 'done' : ($item['required'] ? 'missing' : 'optional');
                        $source = match (true) {
                            count($columns) === 1 => sprintf(__('text.import_guide_from_column'), $columns[0]),
                            count($columns) > 1 => sprintf(__('text.import_guide_from_columns'), implode(', ', $columns)),
                            $item['required'] => __('text.import_guide_missing'),
                            default => '',
                        };
                    @endphp
                    <li data-fields="{{ implode(',', $item['fields']) }}" data-state="{{ $state }}"
                        @if ($item['required']) data-required @endif
                        @if ($item['picker']) data-picker @endif>
                        <i class="lt-checklist-icon" aria-hidden="true"></i>
                        <div>
                            <span class="lt-checklist-label">
                                {{ $item['label'] }}
                                @unless ($item['required'])<span class="lt-checklist-optional">{{ __('label.import_optional') }}</span>@endunless
                            </span>
                            <span class="lt-checklist-source">{{ $source }}</span>
                            <span class="lt-checklist-hint">{{ $item['hint'] }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-slot:right>
    </x-global::layout.content-box>
</div>

<script>
    // Keeps the checklist and the project help in step with the matching as the person changes it.
    (function () {
        const form = document.getElementById('import-fields-form');
        const list = document.getElementById('import-checklist');
        if (!form || !list) {
            return;
        }
        const picker = document.getElementById('importProjectId');

        function update() {
            const columnsOf = {};
            form.querySelectorAll('select[data-import-column]').forEach(function (select) {
                const split = select.value.lastIndexOf('|');
                if (split > -1) {
                    const field = select.value.slice(split + 1);
                    (columnsOf[field] = columnsOf[field] || []).push(select.value.slice(0, split));
                }
            });
            const chosenProject = picker && picker.value ? picker.options[picker.selectedIndex].text : '';

            list.querySelectorAll('li[data-fields]').forEach(function (item) {
                const columns = item.dataset.fields.split(',').flatMap(function (field) { return columnsOf[field] || []; });
                let state = item.hasAttribute('data-required') ? 'missing' : 'optional';
                let source = item.hasAttribute('data-required') ? list.dataset.textMissing : '';
                if (columns.length) {
                    state = 'done';
                    source = (columns.length === 1 ? list.dataset.textColumn : list.dataset.textColumns).replace('%s', columns.join(', '));
                } else if (item.hasAttribute('data-picker') && chosenProject) {
                    state = 'done';
                    source = list.dataset.textPicker.replace('%s', chosenProject);
                }
                item.dataset.state = state;
                item.querySelector('.lt-checklist-source').textContent = source;
            });

            const hasProjectColumn = !!columnsOf.projectName;
            document.querySelectorAll('[data-project-help]').forEach(function (help) {
                help.hidden = (help.dataset.projectHelp === 'column') !== hasProjectColumn;
            });
        }

        form.addEventListener('change', update);
        update();
    })();
</script>

@endsection
