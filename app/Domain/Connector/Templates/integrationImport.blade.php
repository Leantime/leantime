@extends($layout)

@section('content')

@php
    $provider = $provider ?? null;
    $review = $review ?? ['columns' => [], 'rows' => [], 'total' => 0, 'problemRows' => 0, 'hiddenRows' => 0, 'counts' => [], 'projects' => [], 'resolved' => false];
    $generalProblems = $generalProblems ?? [];
    $flags = $flags ?? [];
    $integrationId = isset($integrationId) && is_numeric($integrationId) ? (int) $integrationId : null;
    $needsFix = $flags !== [];
    $counts = $review['counts'];
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress', ['currentStep' => 'parse'])

        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h4">{{ __('headlines.integration_review') }}</x-global::elements.section-header>

        @if ($needsFix)
            {{-- How many rows need a fix; problems about the whole file are listed here, the others sit in their cells. --}}
            <x-global::feedback.alert tone="danger" :title="$review['problemRows'] > 0
                ? sprintf(__($review['problemRows'] === 1 ? 'text.import_check_row_needs_fix' : 'text.import_check_rows_need_fix'), $review['problemRows'], $review['total'])
                : __('text.import_check_file_needs_fix')">
                {{ __('text.integration_resolve_errors') }}
                @if ($generalProblems !== [])
                    <ul>
                        @foreach ($generalProblems as $problem)
                            <li>{{ $problem }}</li>
                        @endforeach
                    </ul>
                @endif
            </x-global::feedback.alert>
        @else
            @php
                // "3 new to-dos for Website Redesign. No problems found."
                $kind = in_array($entity ?? '', ['tickets', 'milestones'], true) ? $entity : 'rows';
                $parts = [];
                foreach (['new', 'updates'] as $count) {
                    if (($counts[$count] ?? 0) > 0) {
                        $parts[] = sprintf(__('text.import_check_'.$count.'_'.$kind.($counts[$count] === 1 ? '_one' : '')), number_format($counts[$count]));
                    }
                }
                $summary = count($parts) === 2 ? sprintf(__('text.import_check_and'), $parts[0], $parts[1]) : ($parts[0] ?? '');
                if (count($review['projects']) === 1) {
                    $summary = sprintf(__('text.import_check_for'), $summary, $review['projects'][0]);
                } elseif (count($review['projects']) > 1) {
                    $summary = sprintf(__('text.import_check_for_projects'), $summary, count($review['projects']));
                }
            @endphp
            <div class="lt-lead">
                <strong>{{ sprintf(__('text.import_check_no_problems'), $summary) }}</strong>
                @if ($review['resolved'])
                    <br />{{ __('text.integration_importing') }}
                @endif
            </div>
            @if ($review['hiddenRows'] > 0)
                {{-- A long file: the table shows the first rows, the counts cover all of them. --}}
                <div class="lt-import-summary">
                    @if (($counts['assigned'] ?? 0) > 0)
                        <x-global::elements.badge>{{ sprintf(__('text.import_badge_assigned'), number_format($counts['assigned'])) }}</x-global::elements.badge>
                    @endif
                    @if (($counts['unassigned'] ?? 0) > 0)
                        <x-global::elements.badge tone="muted">{{ sprintf(__('text.import_badge_unassigned'), number_format($counts['unassigned'])) }}</x-global::elements.badge>
                    @endif
                </div>
            @endif
        @endif

        {{-- One column per matched column of the file, under its Leantime field. The row number is the spreadsheet's. --}}
        @if ($review['rows'] !== [])
            <div class="tw-overflow-x-auto">
            <table class="table table-bordered lt-import-check">
                <thead>
                <tr>
                    <th>{{ __('label.import_row') }}</th>
                    @foreach ($review['columns'] as $column)
                        <th>
                            {{ $column['name'] }}
                            @if ($column['notInFile'] !== null)
                                <span class="lt-cell-note">{{ __('text.import_column_not_in_file') }} <x-global::elements.info-tip :text="$column['notInFile']" /></span>
                            @elseif (strcasecmp($column['name'], $column['source']) !== 0)
                                <span class="lt-cell-note">{{ sprintf(__('text.import_column_from'), $column['source']) }}</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($review['rows'] as $row)
                    <tr>
                        <td>{{ $row['row'] }}</td>
                        @foreach ($row['cells'] as $cell)
                            <td @class(['lt-cell--problem' => $cell['problems'] !== []])>
                                @if ($cell['userId'])
                                    <span class="lt-import-person">
                                        <x-global::elements.avatar :userId="$cell['userId']" :name="$cell['value']" size="sm" />
                                        {{ $cell['value'] }}
                                    </span>
                                @elseif ($cell['muted'])
                                    <span class="lt-cell-note">{{ $cell['value'] }}</span>
                                @elseif ($cell['value'] === '' && $cell['problems'] !== [])
                                    <span class="lt-cell-note">{{ __('text.import_value_empty') }}</span>
                                @else
                                    {{ $cell['value'] }}
                                @endif
                                @if ($cell['note'])
                                    <span class="lt-cell-note">{{ $cell['note'] }}</span>
                                @endif
                                @foreach ($cell['problems'] as $problem)
                                    <span class="lt-cell-problem"><i class="fa fa-circle-exclamation" aria-hidden="true"></i> {{ $problem }}</span>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif

        @if ($review['hiddenRows'] > 0)
            <p class="lt-cell-note">
                {{ $needsFix
                    ? sprintf(__('text.import_check_hidden_fine'), $review['hiddenRows'])
                    : sprintf(__('text.import_check_hidden_more'), count($review['rows']), $review['total']) }}
            </p>
        @endif

        {{-- Back to the matching, and Import only when nothing needs a fix. --}}
        <form method="post" action="{{ $provider->stepUrl('import', $integrationId) }}" class="lt-form-actions">
            <x-global::forms.button tag="a" :link="$provider->stepUrl('fields', $integrationId)" contentRole="tertiary">{{ __($needsFix ? 'buttons.import_back_to_matching' : 'buttons.back') }}</x-global::forms.button>
            @if ($needsFix)
                <x-global::forms.button tag="a" :link="$provider->stepUrl('connect')" contentRole="secondary">{{ __('buttons.import_upload_fixed') }}</x-global::forms.button>
            @else
                {{-- Ties Import to this review: an Import from an older review imports nothing. --}}
                <input type="hidden" name="importToken" value="{{ $importToken ?? '' }}" />
                <x-global::forms.button inputType="submit" contentRole="primary">{{ sprintf(__('buttons.import_n_'.(in_array($entity ?? '', ['tickets', 'milestones'], true) ? $entity : 'rows').($review['total'] === 1 ? '_one' : '')), number_format($review['total'])) }}</x-global::forms.button>
            @endif
        </form>
    </x-global::layout.content-box>
</div>

@endsection
