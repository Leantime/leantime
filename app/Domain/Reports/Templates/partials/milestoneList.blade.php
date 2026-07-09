{{--
    Milestone list used by all report screens in three modes:
    - completed: completion date + outcome narrative (inline-capturable) + key-task drill-down
    - inflight:  progress bar + due date (overdue rows passed in first by the engine)
    - upcoming:  schedule only

    Expects:
    $milestones:       object[] - engine-enriched milestone rows
    $mode:             string - completed|inflight|upcoming
    $showProjects:     bool - prefix rows with project names (rollup screens)
    $allowOutcomeEdit: bool - enable inline outcome capture (project-level screen)
    $effortByMilestone: array<int, float> - hours logged per milestone in the period
    $period:           \Leantime\Domain\Reports\Models\ReportPeriod
    $emptyText:        string - shown when the list is empty
--}}
@php
    $showProjects = $showProjects ?? false;
    $allowOutcomeEdit = $allowOutcomeEdit ?? false;
    $effortByMilestone = $effortByMilestone ?? [];
@endphp

@if (count($milestones) === 0)
    <div class="tw-opacity-60 tw-text-sm tw-py-2">{{ $emptyText }}</div>
@else
    <ul class="reportMilestoneList tw-list-none tw-p-0 tw-m-0 tw-flex tw-flex-col tw-gap-2">
        @foreach ($milestones as $milestone)
            <li class="ticketBox fixed reportMilestone tw-m-0" style="border-left: 4px solid {{ $milestone->tags }};">

                <div class="tw-flex tw-items-baseline tw-gap-2 tw-flex-wrap">
                    @if ($mode === 'completed')
                        <i class="fa fa-check-circle" style="color: var(--green);"></i>
                    @endif
                    <strong>
                        <a href="{{ BASE_URL }}/tickets/editMilestone/{{ $milestone->id }}" class="milestoneModal hideLinkOnPrint">{{ $tpl->escape($milestone->headline) }}</a>
                    </strong>
                    @if ($showProjects)
                        <span class="tw-text-sm tw-opacity-60">{{ $tpl->escape($milestone->projectName) }}</span>
                    @endif

                    <span class="tw-ml-auto tw-text-sm tw-opacity-70 tw-whitespace-nowrap">
                        @if ($mode === 'completed')
                            {{ __('label.completed_on') }} {{ $milestone->completedOn?->formatDateForUser() ?? '—' }}
                        @elseif ($mode === 'upcoming')
                            {{ $milestone->startDate?->formatDateForUser() }} – {{ $milestone->dueDate?->formatDateForUser() ?? '—' }}
                        @else
                            @php $isOverdue = $milestone->dueDate !== null && $milestone->dueDate->isPast(); @endphp
                            <span @if ($isOverdue) style="color: var(--red); font-weight: bold;" @endif>
                                {{ __('label.due') }} {{ $milestone->dueDate?->formatDateForUser() ?? __('text.no_date_defined') }}
                            </span>
                        @endif
                        @if (!empty($effortByMilestone[$milestone->id]))
                            · {{ format($effortByMilestone[$milestone->id])->decimal() }} {{ __('label.hours_short') }}
                        @endif
                    </span>
                </div>

                @if ($mode === 'completed')
                    @include('reports::partials.outcome', ['milestone' => $milestone, 'canEdit' => $allowOutcomeEdit, 'period' => $period])

                    @if (!empty($milestone->keyTasks))
                        <details class="reportKeyTasks tw-mt-1">
                            <summary class="tw-text-sm tw-opacity-60 tw-cursor-pointer">
                                {{ sprintf(__('text.tasks_done_of_total'), $milestone->taskStats['done'], $milestone->taskStats['total']) }}
                            </summary>
                            <ul class="tw-list-none tw-pl-5 tw-pt-1 tw-m-0 tw-text-sm tw-opacity-80">
                                @foreach ($milestone->keyTasks as $task)
                                    <li>
                                        <i class="fa fa-fw {{ $task->isDone ? 'fa-check tw-opacity-60' : 'fa-circle-o' }}"></i>
                                        {{ $tpl->escape($task->headline) }}
                                    </li>
                                @endforeach
                                @if ($milestone->taskStats['total'] > count($milestone->keyTasks))
                                    <li class="tw-opacity-60">{{ sprintf(__('text.and_n_more'), $milestone->taskStats['total'] - count($milestone->keyTasks)) }}</li>
                                @endif
                            </ul>
                        </details>
                    @endif
                @endif

                @if ($mode === 'inflight')
                    <div class="tw-flex tw-items-center tw-gap-2 tw-mt-1.5">
                        <div class="progress tw-flex-1 tw-m-0" style="height: 8px;">
                            <div class="progress-bar progress-bar-success" role="progressbar"
                                 aria-valuenow="{{ round($milestone->percentDone) }}" aria-valuemin="0" aria-valuemax="100"
                                 style="width: {{ round($milestone->percentDone) }}%">
                            </div>
                        </div>
                        <span class="tw-text-sm tw-opacity-70 tw-whitespace-nowrap">{!! sprintf(__('text.percent_complete'), round($milestone->percentDone)) !!}</span>
                    </div>
                    @if ($milestone->taskStats['total'] > 0)
                        <span class="tw-text-xs tw-opacity-60">{{ sprintf(__('text.tasks_done_of_total'), $milestone->taskStats['done'], $milestone->taskStats['total']) }}</span>
                    @endif
                @endif

            </li>
        @endforeach
    </ul>
@endif
