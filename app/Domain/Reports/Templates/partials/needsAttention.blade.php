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
    <x-global::elements.card tone="danger" class="reportNeedsAttention">
        <x-global::elements.section-header as="h5"><i class="fa fa-triangle-exclamation" style="color: var(--red);"></i> {{ __('subtitles.needs_attention') }}</x-global::elements.section-header>

        <ul class="lt-list">
            @foreach ($needsAttention['statusAlerts'] as $project)
                <x-global::elements.list-item :title="$project->name">
                    <x-slot:leading><span class="statusDot" style="background:var(--{{ $project->latestStatus === 'red' ? 'red' : 'yellow' }});"></span></x-slot:leading>
                    {{ __('text.attention_reported_status') }}
                    @if (!empty($project->latestStatusText))
                        — {{ $project->latestStatusText }}
                    @endif
                </x-global::elements.list-item>
            @endforeach

            @foreach ($needsAttention['overdueMilestones'] as $milestone)
                <x-global::elements.list-item icon="fa fa-clock" tone="danger" :title="$milestone->headline">
                    @if ($showProjects){{ $milestone->projectName }} · @endif
                    {{ __('text.attention_overdue_since') }} {{ $milestone->dueDate?->formatDateForUser() }}
                </x-global::elements.list-item>
            @endforeach

            @foreach ($needsAttention['goalsAtRisk'] as $goal)
                <x-global::elements.list-item icon="fa fa-bullseye" tone="warning" :title="$goal->title">
                    {{ $goal->status === 'status_miss' ? __('text.attention_goal_missed') : __('text.attention_goal_at_risk') }}
                    ({{ \Leantime\Core\Support\Format::number((float) $goal->currentValue, 1) }} of {{ \Leantime\Core\Support\Format::number((float) $goal->endValue, 1) }} {{ $goal->metricType ?? '' }})
                </x-global::elements.list-item>
            @endforeach

            @foreach ($needsAttention['staleProjects'] as $project)
                <x-global::elements.list-item icon="fa fa-comment-slash" tone="muted" :title="$project->name">
                    @if (!empty($project->latestStatusDate))
                        {{ sprintf(__('text.attention_no_update_since'), $project->latestStatusDate->formatDateForUser()) }}
                    @else
                        {{ __('text.attention_never_updated') }}
                    @endif
                </x-global::elements.list-item>
            @endforeach
        </ul>
    </x-global::elements.card>
@endif
