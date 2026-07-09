{{--
    "Needs attention" block: red/yellow projects, silent projects, overdue milestones and
    at-risk goals. Rendered first on every report screen; hidden entirely when all is well.

    Expects:
    $needsAttention: array{statusAlerts: object[], staleProjects: object[], overdueMilestones: object[], goalsAtRisk: object[]}
    $showProjects:   bool - prefix items with their project name (rollup screens)
--}}
@php
    $hasAttentionItems = !empty($needsAttention['statusAlerts'])
        || !empty($needsAttention['staleProjects'])
        || !empty($needsAttention['overdueMilestones'])
        || !empty($needsAttention['goalsAtRisk']);
    $showProjects = $showProjects ?? false;
@endphp

@if ($hasAttentionItems)
    <div class="reportSection reportNeedsAttention tw-mb-6 tw-rounded-lg tw-p-4" style="border: 1px solid var(--red); background: color-mix(in srgb, var(--red) 5%, transparent);">
        <h5 class="subtitle tw-mb-3"><i class="fa fa-triangle-exclamation" style="color: var(--red);"></i> {{ __('subtitles.needs_attention') }}</h5>

        <ul class="tw-list-none tw-p-0 tw-m-0 tw-flex tw-flex-col tw-gap-1.5">
            @foreach ($needsAttention['statusAlerts'] as $project)
                <li>
                    <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:var(--{{ $project->latestStatus === 'red' ? 'red' : 'yellow' }});"></span>
                    <strong>{{ $tpl->escape($project->name) }}</strong>
                    {{ __('text.attention_reported_status') }}
                    @if (!empty($project->latestStatusText))
                        — <span class="tw-opacity-80">{{ $tpl->escape($project->latestStatusText) }}</span>
                    @endif
                </li>
            @endforeach

            @foreach ($needsAttention['overdueMilestones'] as $milestone)
                <li>
                    <i class="fa fa-fw fa-clock" style="color: var(--red);"></i>
                    @if ($showProjects)<span class="tw-opacity-70">{{ $tpl->escape($milestone->projectName) }} · </span>@endif
                    <strong>{{ $tpl->escape($milestone->headline) }}</strong>
                    {{ __('text.attention_overdue_since') }} {{ $milestone->dueDate?->formatDateForUser() }}
                </li>
            @endforeach

            @foreach ($needsAttention['goalsAtRisk'] as $goal)
                <li>
                    <i class="fa fa-fw fa-bullseye" style="color: var(--yellow);"></i>
                    <strong>{{ $tpl->escape($goal->title) }}</strong>
                    {{ $goal->status === 'status_miss' ? __('text.attention_goal_missed') : __('text.attention_goal_at_risk') }}
                    <span class="tw-opacity-70">({{ format($goal->currentValue)->decimal() }} / {{ format($goal->endValue)->decimal() }} {{ $tpl->escape($goal->metricType ?? '') }})</span>
                </li>
            @endforeach

            @foreach ($needsAttention['staleProjects'] as $project)
                <li>
                    <i class="fa fa-fw fa-comment-slash tw-opacity-60"></i>
                    <strong>{{ $tpl->escape($project->name) }}</strong>
                    @if (!empty($project->latestStatusDate))
                        {{ sprintf(__('text.attention_no_update_since'), $project->latestStatusDate->formatDateForUser()) }}
                    @else
                        {{ __('text.attention_never_updated') }}
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
