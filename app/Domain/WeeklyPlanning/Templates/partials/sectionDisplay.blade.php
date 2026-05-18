{{--
    Rendered by PlanItems::saveSection() and PlanItems::viewSection().
    Variables: $planId (int), $field (string), $savedValue (string)
    Replaces the outer #section-{field} div (outerHTML swap).
--}}
@php
    $fieldMap = [
        'topPriorities'         => ['icon' => 'fa-star',          'label' => 'weeklyplanning.sections.top_priorities'],
        'winsAndProgress'       => ['icon' => 'fa-trophy',        'label' => 'weeklyplanning.sections.wins_and_progress'],
        'challengesAndBlockers' => ['icon' => 'fa-circle-xmark',  'label' => 'weeklyplanning.sections.challenges_blockers'],
        'managerSupportNeeded'  => ['icon' => 'fa-hands-helping', 'label' => 'weeklyplanning.sections.manager_support'],
        'ideasAndSuggestions'   => ['icon' => 'fa-lightbulb',     'label' => 'weeklyplanning.sections.ideas_suggestions'],
        'nextWeekPriorities'    => ['icon' => 'fa-forward',       'label' => 'weeklyplanning.sections.next_week_priorities'],
    ];
    $meta = $fieldMap[$field] ?? ['icon' => 'fa-edit', 'label' => $field];
@endphp

<div class="wp-card" id="section-{{ $field }}">
    <div class="wp-card-head">
        <h4 class="wp-card-title">
            <i class="fa {{ $meta['icon'] }}"></i> {{ __($meta['label']) }}
        </h4>
        <button class="wp-edit-btn"
                hx-get="{{ BASE_URL }}/hx/weekly-planning/planItems/editSection?planId={{ $planId }}&field={{ $field }}"
                hx-target="#section-{{ $field }}"
                hx-swap="outerHTML"
                title="Edit">
            <i class="fa fa-pencil"></i>
        </button>
    </div>
    <div class="wp-card-body">
        @if($savedValue)
        <div class="wp-section-text">{{ $savedValue }}</div>
        @else
        <span class="wp-section-empty">Nothing added yet</span>
        @endif
    </div>
</div>
