@extends($layout)

@section('content')

@php
    /** @var \Leantime\Domain\CsvImport\Models\ImportTable $table */
    $delimiterNames = [
        ',' => __('label.import_separator_comma'),
        ';' => __('label.import_separator_semicolon'),
        "\t" => __('label.import_separator_tab'),
        '|' => __('label.import_separator_pipe'),
    ];
    $columns = array_values($table->headers);
    $leantimeEntities = $leantimeEntities ?? [];
    $chosenEntity = $chosenEntity ?? '';

    // One line about the file: format, sheet, size and heading row.
    $summary = [
        $table->format === 'xlsx'
            ? __('label.import_format_excel')
            : sprintf(__('label.import_format_csv'), $delimiterNames[$table->delimiter] ?? $table->delimiter),
    ];
    if ($table->sheetName() !== null && count($table->sheetNames) > 1) {
        $summary[] = sprintf(__('text.import_summary_sheet'), $table->sheetName());
    }
    $summary[] = sprintf(__($table->rowCount() === 1 ? 'text.import_summary_row' : 'text.import_summary_rows'), number_format($table->rowCount()));
    $summary[] = sprintf(__(count($columns) === 1 ? 'text.import_summary_column' : 'text.import_summary_columns'), number_format(count($columns)));
    $summary[] = sprintf(__('text.import_summary_heading_row'), $table->headerRow);
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress', ['currentStep' => 'connect'])

        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h4" :icon="$table->format === 'xlsx' ? 'fa fa-file-excel' : 'fa fa-file-csv'">
            {{ $table->fileName ?? __('headlines.import_file_found') }}
            <x-slot:actions>
                <a href="{{ $provider->stepUrl('connect') }}">{{ __('links.import_different_file') }}</a>
            </x-slot:actions>
        </x-global::elements.section-header>

        {{-- The sheet and heading row were found automatically; "Change" opens them. --}}
        <details class="lt-import-file" @if ($table->rowsAbove > 0) open @endif>
            <summary class="lt-cell-note">{{ implode(' · ', $summary) }} · <span class="lt-import-file-change">{{ __('links.import_change') }}</span></summary>
            <form method="post" action="{{ BASE_URL }}/csvImport/preview?integrationId={{ $integrationId }}" class="lt-import-file-form">
                @if (count($table->sheetNames) > 1)
                    <x-global::forms.field layout="inline" for="sheetIndex" :label="__('label.import_sheet')">
                        <x-global::forms.select name="sheetIndex" id="sheetIndex" width="md">
                            @foreach ($table->sheetNames as $index => $name)
                                <option value="{{ $index }}" @if ($index === $table->sheetIndex) selected @endif>{{ $name }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>
                @endif
                <x-global::forms.field layout="inline" for="headerRow" :label="__('label.import_headings_on_row')">
                    <div class="lt-field-row">
                        <x-global::forms.select name="headerRow" id="headerRow" width="sm">
                            @for ($row = 1; $row <= \Leantime\Domain\CsvImport\Services\TableBuilder::HEADER_SCAN_ROWS; $row++)
                                <option value="{{ $row }}" @if ($row === $table->headerRow) selected @endif>{{ $row }}</option>
                            @endfor
                        </x-global::forms.select>
                        <x-global::forms.button inputType="submit" contentRole="secondary">{{ __('buttons.import_change') }}</x-global::forms.button>
                    </div>
                </x-global::forms.field>
            </form>
        </details>

        @if ($table->rowsAbove > 0)
            <x-global::feedback.alert tone="info">
                {{ $table->rowsAbove === 1
                    ? sprintf(__('text.import_row_above_headings'), $table->headerRow)
                    : sprintf(__('text.import_rows_above_headings'), $table->rowsAbove, $table->headerRow) }}
            </x-global::feedback.alert>
        @endif

        @if ($largeFile)
            <x-global::feedback.alert tone="warning">{{ __('text.import_large_file') }}</x-global::feedback.alert>
        @endif

        <form method="post" action="{{ $provider->stepUrl('fields', (int) $integrationId) }}">
            <input type="hidden" name="providerEntities" value="default" />

            {{-- Nothing is chosen until the person picks; the hint says what most files are. --}}
            <div class="lt-import-question">
            <x-global::forms.field for="leantimeEntities" :label="__('label.import_what_is_each_row')" :help="($preset ?? null) !== null ? sprintf(__('text.import_what_is_each_row_preset'), $preset['name']) : __('text.import_what_is_each_row')">
                <x-global::forms.select name="leantimeEntities" id="leantimeEntities" width="md" required aria-describedby="leantimeEntities-help">
                    <option value="" @if ($chosenEntity === '') selected @endif disabled>{{ __('label.import_choose') }}</option>
                    @foreach ($leantimeEntities as $key => $entity)
                        <option value="{{ $key }}" @if ($chosenEntity === $key) selected @endif>{{ $entity['name'] }}</option>
                    @endforeach
                </x-global::forms.select>
            </x-global::forms.field>
            </div>

            <x-global::elements.section-header as="h5">{{ __('headlines.import_first_rows') }}</x-global::elements.section-header>
            {{-- Wide files scroll sideways inside the box instead of widening the page. --}}
            <div class="tw-overflow-x-auto">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('label.import_row') }}</th>
                            @foreach ($columns as $heading)
                                <th>{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview as $row)
                            <tr>
                                <td>{{ $row[\Leantime\Domain\CsvImport\Models\ImportTable::ROW_KEY] ?? '' }}</td>
                                @foreach ($columns as $heading)
                                    <td>{{ \Illuminate\Support\Str::limit((string) ($row[$heading] ?? ''), 80) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- A move this import can't do well (comments, subtasks, several tools) is what the MCP Server plugin is for. --}}
            <x-global::feedback.alert tone="info" icon="fa fa-wand-magic-sparkles" :title="__('headlines.import_complicated')">
                @if ($mcpServerEnabled ?? false)
                    {{ sprintf(__('text.import_complicated_connected'), BASE_URL.'/mcp') }}
                @else
                    {{ __('text.import_complicated_plugin') }}
                    <x-slot:actions>
                        <x-global::forms.button tag="a" link="{{ BASE_URL }}/plugins/marketplace" contentRole="secondary">{{ __('links.import_complicated_plugin') }}</x-global::forms.button>
                    </x-slot:actions>
                @endif
            </x-global::feedback.alert>

            <div class="lt-form-actions">
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/show" contentRole="tertiary">{{ __('buttons.cancel') }}</x-global::forms.button>
                <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.import_next_match') }}</x-global::forms.button>
            </div>
        </form>
    </x-global::layout.content-box>
</div>

@endsection
