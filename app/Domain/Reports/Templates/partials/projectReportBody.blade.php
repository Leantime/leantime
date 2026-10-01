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

    $inFlightMilestones = array_merge($report['milestones']['overdue'], $report['milestones']['inProgress']);
    $fmt = fn ($n) => \Illuminate\Support\Number::format((float) $n, maxPrecision: 1);
@endphp

<div id="reportBody">

    {{-- Header band: status, progress, timeline --}}
    @if ($summary !== null)
        {{-- The verdict pill moved to the pageheader (see project.blade.php), and
             the period label is gone: the picker pill in the nav band above
             already states the active range, so this was the same fact twice. --}}
        <div class="reportHeaderBand">
            <span>
                <strong>{{ round($summary->progress['percent'] ?? 0) }}%</strong> {{ __('label.report_complete') }}
                @php $completionState = $summary->progress['estimatedCompletionState'] ?? 'ready'; @endphp
                {{-- These are explanations of why there is (or isn't) a completion
                     estimate — not calls to action. They were filled primary
                     buttons dropped mid-sentence, which read as the loudest thing
                     on the page while saying "there isn't enough data yet". Quiet
                     inline links instead. --}}
                @if ($completionState === 'needs_more_data')
                    · <a href="{{ BASE_URL }}/tickets/showAll" class="reportHint"><i class="fa fa-thumb-tack"></i> {{ __('label.complete_more_todos') }}</a>
                @elseif ($completionState === 'complete')
                    · <a href="{{ BASE_URL }}/projects/showAll" class="reportHint"><i class="fa fa-suitcase"></i> {{ __('label.project_complete_onto_next') }}</a>
                @elseif (!empty($summary->progress['estimatedCompletionDate']) && $summary->progress['estimatedCompletionDate'] !== false)
                    · {{ __('label.estimated_completion') }} {{ $summary->progress['estimatedCompletionDate'] }}
                @endif
            </span>
        </div>
    @endif

    @include('reports::partials.statTiles', ['tiles' => [
        ['label' => __('label.milestones_completed'), 'value' => $stats['completed'], 'delta' => ['value' => $deltas['completedDelta'], 'goodWhenUp' => true, 'vs' => __('label.vs_prior_period_short')]],
        ['label' => __('label.milestones_in_flight'), 'value' => $stats['inFlight']],
        ['label' => __('label.milestones_overdue'), 'value' => $stats['overdue'], 'tone' => 'danger'],
        ['label' => __('label.hours_logged'), 'value' => $fmt($stats['hoursLogged']), 'delta' => ['value' => $deltas['hoursDelta'], 'goodWhenUp' => null, 'vs' => __('label.vs_prior_period_short')]],
    ]])

    @include('reports::partials.needsAttention', ['needsAttention' => $report['needsAttention'], 'showProjects' => false])

    <div class="reportSection">
        <h5 class="subtitle">{{ __('subtitles.accomplished_this_period') }} <span class="sectionCount">{{ count($report['milestones']['completed']) }}</span></h5>
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

    <div class="reportSection">
        <h5 class="subtitle">{{ __('subtitles.in_flight') }} <span class="sectionCount">{{ count($inFlightMilestones) }}</span></h5>
        @include('reports::partials.milestoneList', [
            'milestones' => $inFlightMilestones,
            'mode' => 'inflight',
            'effortByMilestone' => $report['effort']['byMilestone'],
            'period' => $period,
            'emptyText' => __('text.report_no_inflight_milestones'),
        ])
    </div>

    <div class="reportSection">
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

    <div class="reportSection">
        <h5 class="subtitle">{{ __('subtitles.goals_kpis') }}</h5>
        @include('reports::partials.goalTable', [
            'goals' => $report['goals']['goals'],
            'emptyText' => __('text.report_no_goals'),
        ])
    </div>

    <div class="reportSection">
        <h5 class="subtitle">{{ __('subtitles.status_narrative') }}</h5>
        @include('reports::partials.statusNarrative', [
            'updatesByProject' => $report['statusUpdates'],
            'summaries' => $report['summaries'],
            'showProjects' => false,
            'emptyText' => __('text.report_no_status_updates'),
        ])
    </div>

</div>
