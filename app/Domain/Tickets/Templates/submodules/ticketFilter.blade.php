@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', $currentRoute);
    // Program boards render this partial under a /pgmPro/* route and can pass an explicit
    // self-redirect target. Default to the current route for normal per-project views.
    $searchFormUrl = $searchFormUrl ?? $currentUrlPath;
    $groupBy = $groupByOptions;
    $sortBy = $sortOptions;
    $statusLabels = $allTicketStates;
    $taskToggle = $enableTaskTypeToggle ?? false;
    // Multi-project (program) context: show the project filter + the "project" group-by.
    $showProjectFilter = isset($availableProjects);
    // Kanban shows status as columns, so the status group-by is suppressed. Program kanban
    // templates set $isKanbanView; per-project views derive it from the route.
    $isKanbanView = $isKanbanView ?? ($currentRoute === 'tickets.showKanban');
@endphp

<form action="" method="get" id="ticketSearch">

    <input type="hidden" value="1" name="search"/>
    @unless ($showProjectFilter)
        <input type="hidden" value="{{ session('currentProject') }}" name="projectId" id="projectIdInput"/>
    @endunless

    <div class="filterWrapper" style="display:inline-block; position:relative; vertical-align: bottom; margin-bottom:20px;">
        {{-- Kept as a raw <a> (not forms.button): this button is whitespace-sensitive — the
             </a>@if adjacency below avoids an inline-block gap, and the component would reintroduce
             surrounding whitespace. Migrate once the component guarantees whitespace-tight output. --}}
        <a class="btn btn-link" onclick="leantime.ticketsController.toggleFilterBar();" style="margin-right:5px;"
           data-tippy-content="{{ __('popover.filter') }}">
            <i class="fas fa-filter"></i> Filter{!! $numOfFilters > 0 ? "  <span class='badge badge-primary'>" . $numOfFilters . '</span> ' : '' !!}
            {{-- Please don't change the code formatting below, if not right next to each other it somehow adds a space between the two buttons and increases the distance --}}
        </a>@if ($currentRoute !== 'tickets.roadmap' && $currentRoute != 'tickets.showProjectCalendar')<div class="btn-group viewDropDown">
<button class="btn btn-link dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="{{ __('popover.group_by') }}">
                <span class="fa-solid fa-diagram-project"></span> Group By
                @if ($searchCriteria['groupBy'] != 'all' && $searchCriteria['groupBy'] != '')
                    <span class="badge badge-primary">1</span>
                @endif
            </button>
            <ul class="dropdown-menu">
                @foreach ($groupBy as $input)
                    @if ($input['field'] === 'status' && $isKanbanView)
                        @continue
                    @endif
                    {{-- "Group by project" only makes sense on a multi-project (program) board. --}}
                    @if ($input['field'] === 'projectId' && ! $showProjectFilter)
                        @continue
                    @endif
                    <li>
                        <span class="radio">
                            <input
                                type="radio"
                                name="groupBy"
                                @if ($searchCriteria['groupBy'] == $input['field']) checked='checked' @endif
                                value="{{ $input['field'] }}"
                                id="{{ $input['id'] }}"
                                onclick="leantime.ticketsController.initTicketSearchUrlBuilder('{{ $searchFormUrl }}')"
                            />
                            <label for="{{ $input['id'] }}">{!! __("label.{$input['label']}") !!}</label>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
            @endif
        <div class="filterBar hideOnLoad" style="width:250px;">

            <div class="row-fluid">

                @dispatchEvent('filters.beforeFirstBarField')

                @if ($showProjectFilter)
                    <div class="">
                        <label class="inline">{!! __('label.project') !!}</label>
                        <div class="form-group">
                            <select data-placeholder="{{ __('label.project') }}" title="{{ __('label.project') }}" name="projects" multiple="multiple" class="project-select" id="projectsSelect">
                                <option value="" data-placeholder="true">{!! __('label.project') !!}</option>
                                @foreach ($availableProjects as $projectFilterId => $projectFilterName)
                                    <option value="{{ $projectFilterId }}"
                                        @if (isset($searchCriteria['projects']) && in_array((string) $projectFilterId, explode(',', (string) $searchCriteria['projects']), true)) selected='selected' @endif
                                    >{{ $tpl->escape($projectFilterName) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div class="">
                    <label class="inline">{!! __('label.user') !!}</label>
                    <div class="form-group">
                        <select data-placeholder="{{ __('input.placeholders.filter_by_user') }}"  title="{{ __('input.placeholders.filter_by_user') }}" name="users" multiple="multiple" class="user-select" id="userSelect">
                            <option value="" data-placeholder="true">All Users</option>
                            @foreach ($users as $userRow)
                                <option value="{{ $userRow['id'] }}"
                                    @if ($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && array_search($userRow['id'], explode(',', $searchCriteria['users'])) !== false) selected='selected' @endif
                                >{!! sprintf(__('text.full_name'), $tpl->escape($userRow['firstname']), $tpl->escape($userRow['lastname'])) !!}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{!! __('label.milestone') !!}</label>
                    <div class="form-group">
                        <select data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}" multiple="multiple" title="{{ __('input.placeholders.filter_by_milestone') }}" name="milestone" id="milestoneSelect">
                            <option value="" data-placeholder="true">{!! __('label.all_milestones') !!}</option>
                            <option value="0" @if (isset($searchCriteria['milestone']) && in_array('0', explode(',', (string) $searchCriteria['milestone']), true)) selected='selected' @endif>{!! __('label.not_assigned_to_milestone') !!}</option>
                            @if (is_array($milestones))
                                @foreach ($milestones as $milestoneRow)
                                    <option value="{{ $milestoneRow->id }}"
                                        @if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id) && array_search($milestoneRow->id, explode(',', $searchCriteria['milestone'])) !== false) selected='selected' @endif
                                    >{{ $tpl->escape($milestoneRow->headline) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{!! __('label.todo_type') !!}</label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="{{ __('input.placeholders.filter_by_type') }}" title="{{ __('input.placeholders.filter_by_type') }}" name="type" id="typeSelect">
                            <option value="" data-placeholder="true">{!! __('label.all_types') !!}</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}"
                                    @if (isset($searchCriteria['type']) && array_search($type, explode(',', $searchCriteria['type'])) !== false) selected='selected' @endif
                                >{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{!! __('label.todo_priority') !!}</label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="{{ __('input.placeholders.filter_by_priority') }}" title="{{ __('input.placeholders.filter_by_priority') }}" name="priority" id="prioritySelect">
                            <option value="" data-placeholder="true">{!! __('label.all_priorities') !!}</option>
                            @foreach ($priorities as $priorityKey => $priorityValue)
                                <option value="{{ $priorityKey }}"
                                    @if (isset($searchCriteria['priority']) && array_search($priorityKey, explode(',', $searchCriteria['priority'])) !== false) selected='selected' @endif
                                >{{ $priorityValue }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{!! __('label.todo_status') !!}</label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="{{ __('input.placeholders.filter_by_status') }}" name="status"  multiple="multiple" class="status-select" id="statusSelect">
                            <option value="" data-placeholder="true">All Statuses</option>
                            <option value="not_done" @if ($searchCriteria['status'] !== false && str_contains($searchCriteria['status'], 'not_done')) selected='selected' @endif>{!! __('label.not_done') !!}</option>
                            @foreach ($statusLabels as $key => $label)
                                <option value="{{ $key }}"
                                    @if ($searchCriteria['status'] !== false && array_search((string) $key, explode(',', $searchCriteria['status'])) !== false) selected='selected' @endif
                                >{{ $tpl->escape($label['name']) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="">
                    <div class="form-group">
                        <label class="inline">{!! __('label.search_term') !!}</label>
                        <x-global::forms.text-input name="termInput" id="termInput"
                        style="width: 230px"
                        value="{{ $searchCriteria['term'] }}"
                        placeholder="{{ __('label.search_term') }}" />
                    </div>
                </div>

                <div class="" style="margin-top:15px;">
                    <x-global::forms.button tag="input" inputType="submit" :labelText="__('buttons.search')" name="search" class="form-control" contentRole="primary" />
                </div>

            </div>

        </div>

        @if (isset($taskToggle) && $taskToggle === true)
            <div class="" style="float:right; margin-left:5px; ">
                <input type="checkbox" class="toggle" id="taskTypeToggle" onchange="jQuery('#ticketSearch').submit();" name="showTasks" value="true" {{ ($showTasks === 'true') ? 'checked="checked"' : '' }} style="margin-right:5px;" />
                <label style="text-wrap: nowrap; float:right;">Show Tasks</label>
            </div>
        @endif



    </div>



    <div class="clearall"></div>

    @dispatchEvent('filters.beforeBar')



    @dispatchEvent('filters.beforeFormClose')
</form>

<script>
    jQuery(document).ready(function() {

        new SlimSelect({
            select: '#userSelect',
            settings: {
                placeholderText: 'All Users',
            },
        });
        new SlimSelect({
            select: '#milestoneSelect',
            settings: {
                placeholderText: 'All Milestones',
            },
        });
        new SlimSelect({
            select: '#prioritySelect',
            settings: {
                placeholderText: 'All Priorities',
            },
        });
        new SlimSelect({
            select: '#typeSelect',
            settings: {
                placeholderText: 'All Types',
            },
        });
        new SlimSelect({
            select: '#statusSelect',
            settings: {
                placeholderText: 'All Statuses',
            },
        });
        @if ($showProjectFilter)
        new SlimSelect({
            select: '#projectsSelect',
            settings: {
                placeholderText: @js(__('label.project')),
            },
        });
        @endif

        leantime.ticketsController.initTicketSearchSubmit('{{ $searchFormUrl }}');

    })
</script>
