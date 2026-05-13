@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-calendar-week'">
    <h1>
        {{ $plan['employeeFirstname'] }} {{ $plan['employeeLastname'] }} —
        {{ $plan['weekLabel'] }}, {{ $plan['month'] }}
    </h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Back link --}}
        @if($isTeamLead)
            <a href="{{ BASE_URL }}/weeklyplanning/showTeam" class="btn btn-default btn-sm tw-mb-m">
                <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
            </a>
        @endif

        {{-- Plan header --}}
        <div class="tw-flex tw-flex-wrap tw-gap-m tw-mb-l tw-p-m tw-rounded"
             style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
            <div>
                <small style="color:var(--grey);">{{ __('weeklyplanning.labels.employee') }}</small>
                <strong class="tw-block">{{ $plan['employeeFirstname'] }} {{ $plan['employeeLastname'] }}</strong>
            </div>
            <div>
                <small style="color:var(--grey);">{{ __('weeklyplanning.labels.team_lead') }}</small>
                <strong class="tw-block">{{ $plan['teamLeadFirstname'] }} {{ $plan['teamLeadLastname'] }}</strong>
            </div>
            <div>
                <small style="color:var(--grey);">{{ __('weeklyplanning.labels.week') }}</small>
                <strong class="tw-block">
                    {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }}
                    – {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M Y') }}
                </strong>
            </div>
            @if(!empty($plan['dateOfOneOnOne']))
                <div>
                    <small style="color:var(--grey);">{{ __('weeklyplanning.labels.one_on_one_date') }}</small>
                    <strong class="tw-block">{{ \Carbon\Carbon::parse($plan['dateOfOneOnOne'])->format('d M Y') }}</strong>
                </div>
            @endif
            <div class="tw-ml-auto">
                <span class="label label-{{ $plan['status'] === 'reviewed' ? 'success' : ($plan['status'] === 'active' ? 'primary' : 'default') }} tw-text-sm">
                    {{ __('weeklyplanning.plan_status.'.$plan['status']) }}
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">

                {{-- Assigned Tasks --}}
                <div class="tw-mb-l">
                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-s">
                        <h4 class="widgettitle title-light tw-mb-0">
                            <i class="fa fa-tasks"></i> {{ __('weeklyplanning.sections.assigned_tasks') }}
                        </h4>
                        @if($isTeamLead)
                            <div class="tw-flex tw-gap-xs">
                                <button type="button"
                                        class="btn btn-sm btn-default"
                                        onclick="htmx.ajax('GET', '{{ BASE_URL }}/hx/weeklyplanning/planItems/addForm?planId={{ $plan['id'] }}', {target:'#add-item-container', swap:'innerHTML'})">
                                    <i class="fa fa-plus"></i> {{ __('weeklyplanning.buttons.add_task') }}
                                </button>
                                <button class="btn btn-sm btn-default"
                                        hx-post="{{ BASE_URL }}/hx/weeklyplanning/planItems/carryOver?planId={{ $plan['id'] }}"
                                        hx-target="#plan-items-list"
                                        hx-swap="innerHTML"
                                        hx-confirm="{{ __('weeklyplanning.buttons.carry_over') }}?">
                                    <i class="fa fa-forward"></i> {{ __('weeklyplanning.buttons.carry_over') }}
                                </button>
                            </div>
                        @endif
                    </div>

                    <div id="add-item-container"></div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('weeklyplanning.labels.task') }}</th>
                                    <th style="width:180px;">{{ __('weeklyplanning.labels.status') }}</th>
                                    @if($isTeamLead)
                                        <th style="width:60px;"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="plan-items-list">
                                @include('weeklyplanning::partials.planItemsList', [
                                    'items'      => $items,
                                    'planId'     => $plan['id'],
                                    'isTeamLead' => $isTeamLead,
                                ])
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Text sections: rendered as cards, TL can edit all, employee edits their own --}}
                @php
                    $sections = [
                        ['key' => 'topPriorities',         'icon' => 'fa-star',           'label' => 'weeklyplanning.sections.top_priorities',      'editable' => true],
                        ['key' => 'winsAndProgress',       'icon' => 'fa-trophy',         'label' => 'weeklyplanning.sections.wins_and_progress',   'editable' => true],
                        ['key' => 'challengesAndBlockers', 'icon' => 'fa-ban',            'label' => 'weeklyplanning.sections.challenges_blockers', 'editable' => true],
                        ['key' => 'managerSupportNeeded',  'icon' => 'fa-hands-helping',  'label' => 'weeklyplanning.sections.manager_support',     'editable' => true],
                        ['key' => 'ideasAndSuggestions',   'icon' => 'fa-lightbulb',      'label' => 'weeklyplanning.sections.ideas_suggestions',   'editable' => true],
                        ['key' => 'nextWeekPriorities',    'icon' => 'fa-forward',        'label' => 'weeklyplanning.sections.next_week_priorities','editable' => $isTeamLead],
                    ];
                @endphp

                @foreach($sections as $section)
                    <div class="tw-mb-l" id="section-{{ $section['key'] }}">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-xs">
                            <h4 class="widgettitle title-light tw-mb-0">
                                <i class="fa {{ $section['icon'] }}"></i> {{ __($section['label']) }}
                            </h4>
                            @if($section['editable'])
                                <button class="btn btn-xs btn-link"
                                        hx-get="{{ BASE_URL }}/hx/weeklyplanning/planItems/editSection?planId={{ $plan['id'] }}&field={{ $section['key'] }}"
                                        hx-target="#section-{{ $section['key'] }}"
                                        hx-swap="outerHTML">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            @endif
                        </div>
                        <div class="tw-text-sm" style="white-space:pre-wrap;">
                            {{ $plan[$section['key']] ?? '—' }}
                        </div>
                    </div>
                @endforeach

            </div>

            <div class="col-md-4">

                {{-- Feedback Exchange --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
                    <h4 class="widgettitle title-light">
                        <i class="fa fa-comments"></i> {{ __('weeklyplanning.sections.feedback_exchange') }}
                    </h4>
                    @php
                        $feedbackByType = collect($feedback)->keyBy('type')->toArray();
                    @endphp
                    @foreach($feedbackTypes as $type => $label)
                        @php
                            $canEdit = ($isTeamLead && str_starts_with($type, 'manager_'))
                                    || (! $isTeamLead && str_starts_with($type, 'employee_'));
                        @endphp
                        <div class="tw-mb-s" id="feedback-{{ $type }}">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <small class="tw-font-semibold" style="color:var(--grey);">{{ __($label) }}</small>
                                @if($canEdit)
                                    <button class="btn btn-xs btn-link"
                                            hx-get="{{ BASE_URL }}/hx/weeklyplanning/feedback/editForm?planId={{ $plan['id'] }}&type={{ $type }}"
                                            hx-target="#feedback-{{ $type }}"
                                            hx-swap="outerHTML">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                @endif
                            </div>
                            <p class="tw-text-sm tw-mb-0">{{ $feedbackByType[$type]['message'] ?? '—' }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Growth & Development --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
                    <h4 class="widgettitle title-light">
                        <i class="fa fa-seedling"></i> {{ __('weeklyplanning.sections.growth_development') }}
                    </h4>
                    <div class="tw-mb-xs">
                        <small style="color:var(--grey);">{{ __('weeklyplanning.labels.current_focus') }}</small>
                        <p class="tw-text-sm tw-mb-0">{{ $plan['growthCurrentFocus'] ?? '—' }}</p>
                    </div>
                    <div class="tw-mb-xs">
                        <small style="color:var(--grey);">{{ __('weeklyplanning.labels.support_needed') }}</small>
                        <p class="tw-text-sm tw-mb-0">{{ $plan['growthSupportNeeded'] ?? '—' }}</p>
                    </div>
                    <div>
                        <small style="color:var(--grey);">{{ __('weeklyplanning.labels.next_milestone') }}</small>
                        <p class="tw-text-sm tw-mb-0">{{ $plan['growthNextMilestone'] ?? '—' }}</p>
                    </div>
                </div>

                {{-- Commitments & Follow-ups --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-s">
                        <h4 class="widgettitle title-light tw-mb-0">
                            <i class="fa fa-handshake"></i> {{ __('weeklyplanning.sections.commitments') }}
                        </h4>
                        @if($isTeamLead)
                            <button class="btn btn-xs btn-default"
                                    hx-get="{{ BASE_URL }}/hx/weeklyplanning/planItems/commitmentForm?planId={{ $plan['id'] }}"
                                    hx-target="#commitment-container"
                                    hx-swap="innerHTML">
                                <i class="fa fa-plus"></i>
                            </button>
                        @endif
                    </div>
                    <div id="commitment-container"></div>
                    <div id="commitments-list">
                        @if(count($commitments) === 0)
                            <p class="tw-text-sm" style="color:var(--grey);">{{ __('weeklyplanning.text.no_commitments') }}</p>
                        @else
                            @foreach($commitments as $c)
                                @include('weeklyplanning::partials.commitment', ['c' => $c, 'isTeamLead' => $isTeamLead])
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
