{{--
    Project status report body — swapped by the period picker via HTMX.

    Expects:
    $report:    array - ReportEngine::buildReport() output for [$projectId]
    $period:    \Leantime\Domain\Reports\Models\ReportPeriod
    $projectId: int
--}}
@php
    $summary = $report['summaries'][$projectId] ?? null;
    $stats = $report['stats'];
    $deltas = $report['deltas'];

    $completedDeltaText = sprintf(
        __('text.vs_prior_period'),
        ($deltas['completedDelta'] >= 0 ? '▲ +' : '▼ ') . $deltas['completedDelta']
    );
    $hoursDeltaText = sprintf(
        __('text.vs_prior_period'),
        ($deltas['hoursDelta'] >= 0 ? '▲ +' : '▼ ') . format($deltas['hoursDelta'])->decimal()
    );

    $inFlightMilestones = array_merge($report['milestones']['overdue'], $report['milestones']['inProgress']);
@endphp

<div id="reportBody">

    {{-- Header band: status, progress, timeline --}}
    @if ($summary !== null)
        <div class="tw-flex tw-items-center tw-gap-6 tw-flex-wrap tw-mb-4 tw-text-sm">
            @include('reports::partials.statusPill', ['status' => $summary->latestStatus, 'date' => $summary->latestStatusDate])
            <span>
                <strong>{{ round($summary->progress['percent'] ?? 0) }}%</strong> {{ __('label.report_complete') }}
                @if (!empty($summary->progress['estimatedCompletionDate']) && $summary->progress['estimatedCompletionDate'] !== false)
                    · {{ __('label.estimated_completion') }} {{ $summary->progress['estimatedCompletionDate'] }}
                @endif
            </span>
            <span class="tw-opacity-70">{{ $period->label() }}</span>
        </div>
    @endif

    @include('reports::partials.statTiles', ['tiles' => [
        ['label' => __('label.milestones_completed'), 'value' => $stats['completed'], 'delta' => $completedDeltaText],
        ['label' => __('label.milestones_in_flight'), 'value' => $stats['inFlight'], 'delta' => null],
        ['label' => __('label.milestones_overdue'), 'value' => $stats['overdue'], 'delta' => null, 'tone' => 'danger'],
        ['label' => __('label.hours_logged'), 'value' => format($stats['hoursLogged'])->decimal(), 'delta' => $hoursDeltaText],
    ]])

    @include('reports::partials.needsAttention', ['needsAttention' => $report['needsAttention'], 'showProjects' => false])

    <div class="reportSection">
        <h5 class="subtitle">{{ __('subtitles.accomplished_this_period') }}</h5>
        @include('reports::partials.milestoneList', [
            'milestones' => $report['milestones']['completed'],
            'mode' => 'completed',
            'allowOutcomeEdit' => true,
            'effortByMilestone' => $report['effort']['byMilestone'],
            'period' => $period,
            'emptyText' => __('text.report_no_completed_milestones'),
        ])

        @include('reports::partials.changedThisPeriod', ['slippage' => $report['milestones']['slippage'], 'showProjects' => false])
    </div>

    <div class="reportSection tw-mt-6">
        <h5 class="subtitle">{{ __('subtitles.in_flight') }}</h5>
        @include('reports::partials.milestoneList', [
            'milestones' => $inFlightMilestones,
            'mode' => 'inflight',
            'effortByMilestone' => $report['effort']['byMilestone'],
            'period' => $period,
            'emptyText' => __('text.report_no_inflight_milestones'),
        ])
    </div>

    <div class="reportSection tw-mt-6">
        <h5 class="subtitle">{{ __('subtitles.coming_up') }}</h5>
        @if (count($report['milestones']['upcomingByQuarter']) === 0)
            <div class="tw-opacity-60 tw-text-sm tw-py-2">{{ __('text.report_no_upcoming_milestones') }}</div>
        @else
            @foreach ($report['milestones']['upcomingByQuarter'] as $quarterLabel => $quarterMilestones)
                <h6 class="tw-font-bold tw-opacity-70 tw-mt-3 tw-mb-1">{{ $quarterLabel }}</h6>
                @include('reports::partials.milestoneList', [
                    'milestones' => $quarterMilestones,
                    'mode' => 'upcoming',
                    'period' => $period,
                    'emptyText' => '',
                ])
            @endforeach
        @endif
    </div>

    <div class="reportSection tw-mt-6">
        <h5 class="subtitle">{{ __('subtitles.goals_kpis') }}</h5>
        @include('reports::partials.goalTable', [
            'goals' => $report['goals']['goals'],
            'emptyText' => __('text.report_no_goals'),
        ])
    </div>

    <div class="reportSection tw-mt-6">
        <h5 class="subtitle">{{ __('subtitles.status_narrative') }}</h5>
        @include('reports::partials.statusNarrative', [
            'updatesByProject' => $report['statusUpdates'],
            'summaries' => $report['summaries'],
            'showProjects' => false,
            'emptyText' => __('text.report_no_status_updates'),
        ])
    </div>

</div>
