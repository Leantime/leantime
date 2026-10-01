@extends($layout)

@section('content')

    @php
        $summary = $report['summaries'][$projectId] ?? null;
    @endphp

    {{-- Header rule (2026-08-03): the title block is the LEFT cluster and page
         actions sit in .pageheader-right as a SIBLING of .pagetitle — never in a
         bootstrap .row inside it, which added the grid's negative margins and
         pushed the card's content out of line with the content card below. --}}
    <x-global::pageheader :icon="'fa fa-chart-bar'">
        <h5>{{ session('currentProjectClient') ? session('currentProjectClient') . ' // ' : '' }}{{ session('currentProjectName') }}</h5>
        <h1>{!! __('headlines.status_report') !!}</h1>

        {{-- Verdict lives in the header, matching the strategy/program report
             decks (rd-hdr .verdict). It was buried in a text line inside the
             body; the header is where a reader looks for "what about it".
             Safe to sit outside #reportBody (the HTMX swap target): the latest
             status update and the progress figure are project-level state, not
             period-scoped, so changing the period can't make this stale. --}}
        <x-slot:actions>
            @if ($summary !== null)
                @include('reports::partials.statusPill', [
                    'status' => $summary->latestStatus,
                    'date' => $summary->latestStatusDate,
                ])
            @endif

            <x-global::forms.button tag="button" inputType="button" onclick="window.print();"
                                    class="hideOnPrint"
                                    leadingVisual="fa fa-print" :labelText="__('label.print_report')" />
        </x-slot:actions>
    </x-global::pageheader>

    <div class="maincontent">

        {{-- Nav band on the gradient, between the header card and the content
             card — the same bar the boards and the program report use, so the
             report family reads as one design. Was a bare <ul class="tabs-list">
             INSIDE the white card: that class has no CSS anywhere in the app, so
             it rendered as a raw list complete with disc bullets. --}}
        <div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
            <nav class="lt-tabs-group" aria-label="{{ __('label.status_report_tab') }} / {{ __('label.delivery_metrics_tab') }}">
                <ul>
                    <li class="active"><a href="{{ BASE_URL }}/reports/project">{{ __('label.status_report_tab') }}</a></li>
                    <li><a href="{{ BASE_URL }}/reports/show" preload="mouseover">{{ __('label.delivery_metrics_tab') }}</a></li>
                </ul>
            </nav>

            <div class="lt-tabs-actions">
                {{-- Same control as the strategy/program report decks — one pill
                     with the resolved range, not the old row of four separate
                     btn-secondary pills plus a loose text label. --}}
                <x-global::periodpicker
                    :period="$period"
                    :url="BASE_URL.'/reports/project'"
                    :hxUrl="BASE_URL.'/hx/reports/projectReport/get'"
                    target="#reportBody"
                    :hints="[
                        \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_LAST_QUARTER => __('stakeholder.period.default_hint'),
                        \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_THIS_QUARTER => __('stakeholder.period.in_progress_hint'),
                        \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_NEXT_QUARTER => __('stakeholder.period.upcoming_hint'),
                    ]" />
            </div>
        </div>

        <div class="maincontentinner">

            {!! $tpl->displayNotification() !!}

            @include('reports::partials.projectReportBody', ['report' => $report, 'period' => $period, 'projectId' => $projectId])

        </div>
    </div>

@endsection
