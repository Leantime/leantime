{{--
    Rendered by PlanItems::saveSection() and PlanItems::viewSection().
    Variables: $planId (int), $field (string), $savedValue (string)
    Replaces the outer #section-{field} div (outerHTML swap).
--}}
@php
    $fieldMap = [
        'topPriorities'         => ['icon' => 'fa-star',          'label' => 'weeklyplanning.sections.top_priorities'],
        'winsAndProgress'       => ['icon' => 'fa-trophy',        'label' => 'weeklyplanning.sections.wins_and_progress'],
        'challengesAndBlockers' => ['icon' => 'fa-ban',           'label' => 'weeklyplanning.sections.challenges_blockers'],
        'managerSupportNeeded'  => ['icon' => 'fa-hands-helping', 'label' => 'weeklyplanning.sections.manager_support'],
        'ideasAndSuggestions'   => ['icon' => 'fa-lightbulb',     'label' => 'weeklyplanning.sections.ideas_suggestions'],
        'nextWeekPriorities'    => ['icon' => 'fa-forward',       'label' => 'weeklyplanning.sections.next_week_priorities'],
    ];
    $meta = $fieldMap[$field] ?? ['icon' => 'fa-edit', 'label' => $field];
@endphp

<div class="tw-mb-l" id="section-{{ $field }}">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-xs">
        <h4 class="widgettitle title-light tw-mb-0">
            <i class="fa {{ $meta['icon'] }}"></i> {{ __($meta['label']) }}
        </h4>
        <button class="btn btn-xs btn-link"
                hx-get="{{ BASE_URL }}/hx/weekly-planning/planItems/editSection?planId={{ $planId }}&field={{ $field }}"
                hx-target="#section-{{ $field }}"
                hx-swap="outerHTML">
            <i class="fa fa-pencil"></i>
        </button>
    </div>
    <div class="tw-text-sm" style="white-space:pre-wrap;">{{ $savedValue ?: '—' }}</div>
</div>
