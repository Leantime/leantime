{{--
    Goals & KPIs table: status dot, metric ("18 of 40 graduates"), progress bar.
    The linked-milestone column only renders when at least one goal links a milestone.

    Expects:
    $goals:        object[] - engine-enriched goal rows (goalProgress resolved incl. roll-ups)
    $showProjects: bool
    $emptyText:    string
--}}
@php
    $showProjects = $showProjects ?? false;
    $goalStatusColors = [
        'status_ontrack' => 'var(--green)',
        'status_atrisk' => 'var(--yellow)',
        'status_miss' => 'var(--red)',
    ];
    $hasMilestoneLinks = false;
    foreach ($goals as $goalRow) {
        if (!empty($goalRow->milestoneHeadline)) {
            $hasMilestoneLinks = true;
            break;
        }
    }
    $fmt = fn ($n) => \Illuminate\Support\Number::format((float) $n, maxPrecision: 1);
@endphp

@if (count($goals) === 0)
    <div class="tw-opacity-60 tw-text-sm tw-py-2">{{ $emptyText }}</div>
@else
    <table class="reportTable reportGoalTable">
        <thead>
            <tr>
                <th>{{ __('label.goal') }}</th>
                @if ($showProjects)<th>{{ __('label.project') }}</th>@endif
                <th class="numCol">{{ __('label.metric') }}</th>
                <th style="width: 28%;">{{ __('label.progress') }}</th>
                @if ($hasMilestoneLinks)<th>{{ __('label.linked_milestone') }}</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach ($goals as $goal)
                <tr>
                    <td>
                        <span class="statusDot" style="background:{{ $goalStatusColors[$goal->status] ?? 'var(--grey)' }};"></span>
                        <strong>{{ $tpl->escape($goal->title) }}</strong>
                        @if ($goal->setting === 'linkAndReport')
                            <span class="cellNote"><i class="fa fa-sitemap tw-opacity-60"></i>
                                @if (!empty($goal->childGoalCount))
                                    {{ sprintf(__('text.fed_by_n_goals'), $goal->childGoalCount) }}
                                @else
                                    {{ __('text.goal_rollup_tooltip') }}
                                @endif
                            </span>
                        @endif
                    </td>
                    @if ($showProjects)
                        <td class="tw-opacity-70">{{ $tpl->escape($goal->boardProjectName ?? $goal->projectName ?? '') }}</td>
                    @endif
                    <td class="numCol">
                        <strong>{{ $fmt($goal->currentValue) }}</strong> <span class="tw-opacity-60">of {{ $fmt($goal->endValue) }} {{ $tpl->escape($goal->metricType ?? '') }}</span>
                    </td>
                    <td>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <div class="progress tw-flex-1 tw-m-0" style="height: 6px;">
                                <div class="progress-bar progress-bar-success" role="progressbar"
                                     aria-valuenow="{{ round($goal->goalProgress) }}" aria-valuemin="0" aria-valuemax="100"
                                     style="width: {{ round($goal->goalProgress) }}%">
                                </div>
                            </div>
                            <span class="tw-text-sm tw-opacity-70" style="font-variant-numeric: tabular-nums;">{{ round($goal->goalProgress) }}%</span>
                        </div>
                    </td>
                    @if ($hasMilestoneLinks)
                        <td class="tw-opacity-70">{{ $tpl->escape($goal->milestoneHeadline ?? '') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
