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

        {{-- What was found, in one line: format · sheet · rows · heading row · columns --}}
        <p style="font-size: var(--font-size-l); margin: 10px 0;">
            @if ($table->format === 'xlsx')
                {{ __('label.import_format_excel') }}
                @if ($table->sheetName() !== null)
                    · {{ sprintf(__('label.import_sheet_named'), $table->sheetName()) }}
                @endif
            @else
                {{ sprintf(__('label.import_format_csv'), $delimiterNames[$table->delimiter] ?? $table->delimiter) }}
            @endif
            · {{ sprintf(__('label.import_row_count'), number_format($table->rowCount())) }}
            · {{ sprintf(__('label.import_heading_row'), $table->headerRow) }}
            · {{ sprintf(__('label.import_column_count'), count($columns)) }}
        </p>

        @if ($largeFile)
            <p>{{ __('text.import_large_file') }}</p>
        @endif

        {{-- The person can correct the sheet and heading row; the file is read again. --}}
        <form method="post" action="{{ BASE_URL }}/csvImport/preview?integrationId={{ $integrationId }}" style="margin: 15px 0;">
            @if (count($table->sheetNames) > 1)
                <label for="sheetIndex" style="display:inline-block; margin-right: 5px;">{{ __('label.import_sheet') }}</label>
                <select name="sheetIndex" id="sheetIndex" style="margin-right: 15px;">
                    @foreach ($table->sheetNames as $index => $name)
                        <option value="{{ $index }}" @if ($index === $table->sheetIndex) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            @endif

            <label for="headerRow" style="display:inline-block; margin-right: 5px;">{{ __('label.import_headings_on_row') }}</label>
            <select name="headerRow" id="headerRow" style="margin-right: 15px;">
                @for ($row = 1; $row <= \Leantime\Domain\CsvImport\Services\TableBuilder::HEADER_SCAN_ROWS; $row++)
                    <option value="{{ $row }}" @if ($row === $table->headerRow) selected @endif>{{ $row }}</option>
                @endfor
            </select>

            <x-global::forms.button inputType="submit" contentRole="secondary">{{ __('buttons.import_change') }}</x-global::forms.button>
        </form>

        <x-global::elements.section-header as="h5">{{ __('headlines.import_first_rows') }}</x-global::elements.section-header>
        <div style="overflow-x: auto; margin: 10px 0 20px;">
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
