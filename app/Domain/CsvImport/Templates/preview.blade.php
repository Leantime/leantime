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
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {!! __('headlines.connector') !!} // {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress')
    </x-global::layout.content-box>

    <x-global::layout.content-box>
        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h5">{{ __('headlines.import_file_found') }}</x-global::elements.section-header>

        {{-- What was found. The sheet and the heading row are in the form below, where they can be changed. --}}
        <x-global::elements.details-list layout="inline" :items="[
            __('label.import_file_type') => $table->format === 'xlsx'
                ? __('label.import_format_excel')
                : sprintf(__('label.import_format_csv'), $delimiterNames[$table->delimiter] ?? $table->delimiter),
            __('label.import_rows') => number_format($table->rowCount()),
            __('label.import_columns') => number_format(count($columns)),
        ]" />

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

        {{-- The person can correct the sheet and heading row; the file is read again. --}}
        <form method="post" action="{{ BASE_URL }}/csvImport/preview?integrationId={{ $integrationId }}">
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

        <x-global::elements.section-header as="h5">{{ __('headlines.import_first_rows') }}</x-global::elements.section-header>
        {{-- Wide files scroll sideways inside the box instead of widening the page. --}}
        <div class="tw-overflow-x-auto tw-mb-4">
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

        <div class="left">
            <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}" contentRole="tertiary">{{ __('links.import_different_file') }}</x-global::forms.button>
        </div>
        <div class="right">
            <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}&step=entity&integrationId={{ $integrationId }}" contentRole="primary">{{ __('buttons.next') }}</x-global::forms.button>
        </div>
        <div class="clearall"></div>
    </x-global::layout.content-box>
</div>

@endsection
