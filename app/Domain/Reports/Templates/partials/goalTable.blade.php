{{--
    Goals & KPIs table: metric ("42 of 60"), progress bar, status dot, linked milestone.

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
@endphp

@if (count($goals) === 0)
    <div class="tw-opacity-60 tw-text-sm tw-py-2">{{ $emptyText }}</div>
@else
    <table class="table reportGoalTable tw-w-full">
        <thead>
            <tr>
                <th>{{ __('label.goal') }}</th>
                @if ($showProjects)<th>{{ __('label.project') }}</th>@endif
                <th>{{ __('label.metric') }}</th>
                <th style="width: 25%;">{{ __('label.progress') }}</th>
                <th>{{ __('label.linked_milestone') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($goals as $goal)
                <tr>
                    <td>
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $goalStatusColors[$goal->status] ?? 'var(--grey)' }};"></span>
                        <strong>{{ $tpl->escape($goal->title) }}</strong>
                        @if ($goal->setting === 'linkAndReport')
                            <span class="tw-text-xs tw-opacity-60" title="{{ __('text.goal_rollup_tooltip') }}"><i class="fa fa-sitemap"></i></span>
                            @if (!empty($goal->childGoalCount))
                                <span class="tw-text-xs tw-opacity-60">{{ sprintf(__('text.fed_by_n_goals'), $goal->childGoalCount) }}</span>
                            @endif
                        @endif
                    </td>
                    @if ($showProjects)
                        <td class="tw-text-sm tw-opacity-70">{{ $tpl->escape($goal->boardProjectName ?? $goal->projectName ?? '') }}</td>
                    @endif
                    <td class="tw-whitespace-nowrap">
                        {{ format($goal->currentValue)->decimal() }} / {{ format($goal->endValue)->decimal() }}
                        <span class="tw-opacity-60">{{ $tpl->escape($goal->metricType ?? '') }}</span>
                    </td>
                    <td>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <div class="progress tw-flex-1 tw-m-0" style="height: 8px;">
                                <div class="progress-bar progress-bar-success" role="progressbar"
                                     aria-valuenow="{{ round($goal->goalProgress) }}" aria-valuemin="0" aria-valuemax="100"
                                     style="width: {{ round($goal->goalProgress) }}%">
                                </div>
                            </div>
                            <span class="tw-text-sm tw-opacity-70">{{ round($goal->goalProgress) }}%</span>
                        </div>
                    </td>
                    <td class="tw-text-sm tw-opacity-70">{{ $tpl->escape($goal->milestoneHeadline ?? '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
