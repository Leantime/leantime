@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', $currentRoute);
    $groupBy = $tpl->get('groupByOptions');
    $sortBy = $tpl->get('sortOptions');
    $searchCriteria = $tpl->get('searchCriteria');
    $statusLabels = $tpl->get('allTicketStates');
    $taskToggle = $tpl->get('enableTaskTypeToggle');
@endphp

<form action="" method="get" id="ticketSearch">

    <input type="hidden" value="1" name="search"/>
    <input type="hidden" value="{{ session('currentProject') }}" name="projectId" id="projectIdInput"/>

    <div class="filterWrapper" style="display:inline-block; position:relative; vertical-align: bottom; margin-bottom:20px;">
        <x-globals::forms.button tag="button" type="link" onclick="leantime.ticketsController.toggleFilterBar();" style="margin-right:5px;" data-tippy-content="{{ __('popover.filter') }}">
            <i class="fas fa-filter"></i> Filter{!! $tpl->get('numOfFilters') > 0 ? "  <span class='badge badge-primary'>" . $tpl->get('numOfFilters') . '</span> ' : '' !!}
        </x-globals::forms.button>@if($currentRoute !== 'tickets.roadmap' && $currentRoute != 'tickets.showProjectCalendar')<x-globals::elements.link-dropdown triggerClass="btn btn-link" align="end" data-tippy-content="{{ __('popover.group_by') }}">
                <x-slot:label>
                    <span class="fa-solid fa-diagram-project"></span> Group By
                    @if($searchCriteria['groupBy'] != 'all' && $searchCriteria['groupBy'] != '')
                        <span class="badge badge-primary">1</span>
                    @endif
                </x-slot:label>
                @foreach($groupBy as $input)
                    @if($input['field'] === 'status' && $currentRoute === 'tickets.showKanban')
                        @continue
                    @endif
                    <li>
                        <span class="radio">
                            <x-globals::forms.radio name="groupBy" value="{{ $input['field'] }}" id="{{ $input['id'] }}"
                                :checked="$searchCriteria['groupBy'] == $input['field']"
                                onclick="leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')"
                            />
                            <label for="{{ $input['id'] }}">{{ __("label.{$input['label']}") }}</label>
                        </span>
                    </li>
                @endforeach
            </x-globals::elements.link-dropdown>
        @endif

        <div class="filterBar hideOnLoad" style="width:250px;">
            <div class="row-fluid">

                @dispatchEvent('filters.beforeFirstBarField')

                <div class="">
                    <label class="inline">{{ __('label.user') }}</label>
                    <div class="form-group">
                        <x-globals::forms.select :bare="true" data-placeholder="{{ __('input.placeholders.filter_by_user') }}" title="{{ __('input.placeholders.filter_by_user') }}" name="users" multiple="multiple" class="user-select" id="userSelect">
                            <option value="" data-placeholder="true">All Users</option>
                            @foreach($tpl->get('users') as $userRow)
                                <option value="{{ $userRow['id'] }}"
                                    {{ ($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && array_search($userRow['id'], explode(',', $searchCriteria['users'])) !== false) ? "selected='selected'" : '' }}
                                >{{ sprintf(__('text.full_name'), e($userRow['firstname']), e($userRow['lastname'])) }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{{ __('label.milestone') }}</label>
                    <div class="form-group">
                        <x-globals::forms.select :bare="true" data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}" multiple="multiple" title="{{ __('input.placeholders.filter_by_milestone') }}" name="milestone" id="milestoneSelect">
                            <option value="" data-placeholder="true">{{ __('label.all_milestones') }}</option>
                            @if(is_array($tpl->get('milestones')))
                                @foreach($tpl->get('milestones') as $milestoneRow)
                                    <option value="{{ $milestoneRow->id }}"
                                        {{ (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id) && array_search($milestoneRow->id, explode(',', $searchCriteria['milestone'])) !== false) ? "selected='selected'" : '' }}
                                    >{{ e($milestoneRow->headline) }}</option>
                                @endforeach
                            @endif
                        </x-globals::forms.select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{{ __('label.todo_type') }}</label>
                    <div class="form-group">
                        <x-globals::forms.select :bare="true" multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_type') }}" title="{{ __('input.placeholders.filter_by_type') }}" name="type" id="typeSelect">
                            <option value="" data-placeholder="true">{{ __('label.all_types') }}</option>
                            @foreach($tpl->get('types') as $type)
                                <option value="{{ $type }}"
                                    {{ (isset($searchCriteria['type']) && array_search($type, explode(',', $searchCriteria['type'])) !== false) ? "selected='selected'" : '' }}
                                >{{ $type }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{{ __('label.todo_priority') }}</label>
                    <div class="form-group">
                        <x-globals::forms.select :bare="true" multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_priority') }}" title="{{ __('input.placeholders.filter_by_priority') }}" name="priority" id="prioritySelect">
                            <option value="" data-placeholder="true">{{ __('label.all_priorities') }}</option>
                            @foreach($tpl->get('priorities') as $priorityKey => $priorityValue)
                                <option value="{{ $priorityKey }}"
                                    {{ (isset($searchCriteria['priority']) && array_search($priorityKey, explode(',', $searchCriteria['priority'])) !== false) ? "selected='selected'" : '' }}
                                >{{ $priorityValue }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </div>
                </div>

                <div class="">
                    <label class="inline">{{ __('label.todo_status') }}</label>
                    <div class="form-group">
                        <x-globals::forms.select :bare="true" multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_status') }}" name="status" class="status-select" id="statusSelect">
                            <option value="" data-placeholder="true">All Statuses</option>
                            <option value="not_done" {{ ($searchCriteria['status'] !== false && str_contains($searchCriteria['status'], 'not_done')) ? "selected='selected'" : '' }}>{{ __('label.not_done') }}</option>
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}"
                                    {{ ($searchCriteria['status'] !== false && array_search((string) $key, explode(',', $searchCriteria['status'])) !== false) ? "selected='selected'" : '' }}
                                >{{ e($label['name']) }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </div>
                </div>

                <div class="">
                    <div class="form-group">
                        <label class="inline">{{ __('label.search_term') }}</label>
                        <x-globals::forms.input :bare="true" type="text" name="termInput" id="termInput"
                               style="width: 230px"
                               value="{{ $searchCriteria['term'] }}"
                               placeholder="{{ __('label.search_term') }}" />
                    </div>
                </div>

                <div class="" style="margin-top:15px;">
                    <x-globals::forms.button submit type="primary" name="search" class="form-control">{{ __('buttons.search') }}</x-globals::forms.button>
                </div>

            </div>
        </div>

        @if(isset($taskToggle) && $taskToggle === true)
            <div class="" style="float:right; margin-left:5px; ">
                <input type="checkbox" class="toggle" id="taskTypeToggle" onchange="jQuery('#ticketSearch').submit();" name="showTasks" value="true" {{ ($tpl->get('showTasks') === 'true') ? 'checked="checked"' : '' }} style="margin-right:5px;" />
                <label for="taskTypeToggle" style="text-wrap: nowrap; float:right;">Show Tasks</label>
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

        leantime.ticketsController.initTicketSearchSubmit('{{ $currentUrlPath }}');

    })
</script>
