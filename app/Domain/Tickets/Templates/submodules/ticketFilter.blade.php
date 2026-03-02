@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', $currentRoute);
    $groupBy = $tpl->get('groupByOptions');
    $sortBy = $tpl->get('sortOptions');
    $searchCriteria = $tpl->get('searchCriteria');
    $statusLabels = $tpl->get('allTicketStates');
    $taskToggle = $tpl->get('enableTaskTypeToggle');

    // Helper: normalize filter value to array (handles both string and array from form submissions)
    $toArray = fn($value) => is_array($value) ? $value : explode(',', (string) $value);
@endphp

<form action="" method="get" id="ticketSearch">

    <input type="hidden" value="1" name="search"/>
    <input type="hidden" value="{{ session('currentProject') }}" name="projectId" id="projectIdInput"/>

    <div class="filterWrapper" style="display:inline-block; position:relative; vertical-align: bottom; margin-bottom:20px;">
        <x-globals::forms.button tag="button" type="link" onclick="event.preventDefault(); leantime.ticketsController.toggleFilterBar();" style="margin-right:5px;" data-tippy-content="{{ __('popover.filter') }}">
            <x-global::elements.icon name="filter_list" /> Filter
            @if($tpl->get('numOfFilters') > 0)
                <x-globals::elements.badge color="primary">{{ $tpl->get('numOfFilters') }}</x-globals::elements.badge>
            @endif
        </x-globals::forms.button>@if($currentRoute !== 'tickets.roadmap' && $currentRoute != 'tickets.showProjectCalendar')<x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" trigger-class="btn btn-link" align="end" data-tippy-content="{{ __('popover.group_by') }}">
                <x-slot:label>
                    <x-global::elements.icon name="account_tree" /> Group By
                    @if($searchCriteria['groupBy'] != 'all' && $searchCriteria['groupBy'] != '')
                        <x-globals::elements.badge color="primary">1</x-globals::elements.badge>
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
            </x-globals::actions.dropdown-menu>
        @endif

        <div class="filterBar hideOnLoad">
            <div class="row-fluid">

                @dispatchEvent('filters.beforeFirstBarField')

                <x-globals::forms.form-field label-text="{{ __('label.user') }}" name="userSelect">
                    <x-globals::forms.select :bare="true" data-placeholder="{{ __('input.placeholders.filter_by_user') }}" title="{{ __('input.placeholders.filter_by_user') }}" name="users" multiple="multiple" class="user-select" id="userSelect">
                        <option value="" data-placeholder="true">All Users</option>
                        @foreach($tpl->get('users') as $userRow)
                            <option value="{{ $userRow['id'] }}"
                                {{ ($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && in_array($userRow['id'], $toArray($searchCriteria['users']))) ? "selected='selected'" : '' }}
                            >{{ sprintf(__('text.full_name'), e($userRow['firstname']), e($userRow['lastname'])) }}</option>
                        @endforeach
                    </x-globals::forms.select>
                </x-globals::forms.form-field>

                <x-globals::forms.form-field label-text="{{ __('label.milestone') }}" name="milestoneSelect">
                    <x-globals::forms.select :bare="true" data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}" multiple="multiple" title="{{ __('input.placeholders.filter_by_milestone') }}" name="milestone" id="milestoneSelect">
                        <option value="" data-placeholder="true">{{ __('label.all_milestones') }}</option>
                        @if(is_array($tpl->get('milestones')))
                            @foreach($tpl->get('milestones') as $milestoneRow)
                                <option value="{{ $milestoneRow->id }}"
                                    {{ (isset($searchCriteria['milestone']) && in_array($milestoneRow->id, $toArray($searchCriteria['milestone']))) ? "selected='selected'" : '' }}
                                >{{ e($milestoneRow->headline) }}</option>
                            @endforeach
                        @endif
                    </x-globals::forms.select>
                </x-globals::forms.form-field>

                <x-globals::forms.form-field label-text="{{ __('label.todo_type') }}" name="typeSelect">
                    <x-globals::forms.select :bare="true" multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_type') }}" title="{{ __('input.placeholders.filter_by_type') }}" name="type" id="typeSelect">
                        <option value="" data-placeholder="true">{{ __('label.all_types') }}</option>
                        @foreach($tpl->get('types') as $type)
                            <option value="{{ $type }}"
                                {{ (isset($searchCriteria['type']) && in_array($type, $toArray($searchCriteria['type']))) ? "selected='selected'" : '' }}
                            >{{ $type }}</option>
                        @endforeach
                    </x-globals::forms.select>
                </x-globals::forms.form-field>

                <x-globals::forms.form-field label-text="{{ __('label.todo_priority') }}" name="prioritySelect">
                    <x-globals::forms.select :bare="true" multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_priority') }}" title="{{ __('input.placeholders.filter_by_priority') }}" name="priority" id="prioritySelect">
                        <option value="" data-placeholder="true">{{ __('label.all_priorities') }}</option>
                        @foreach($tpl->get('priorities') as $priorityKey => $priorityValue)
                            <option value="{{ $priorityKey }}"
                                {{ (isset($searchCriteria['priority']) && in_array($priorityKey, $toArray($searchCriteria['priority']))) ? "selected='selected'" : '' }}
                            >{{ $priorityValue }}</option>
                        @endforeach
                    </x-globals::forms.select>
                </x-globals::forms.form-field>

                <x-globals::forms.form-field label-text="{{ __('label.todo_status') }}" name="statusSelect">
                    <x-globals::forms.select :bare="true" multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_status') }}" name="status" class="status-select" id="statusSelect">
                        <option value="" data-placeholder="true">All Statuses</option>
                        <option value="not_done" {{ ($searchCriteria['status'] !== false && in_array('not_done', $toArray($searchCriteria['status']))) ? "selected='selected'" : '' }}>{{ __('label.not_done') }}</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}"
                                {{ ($searchCriteria['status'] !== false && in_array((string) $key, $toArray($searchCriteria['status']))) ? "selected='selected'" : '' }}
                            >{{ e($label['name']) }}</option>
                        @endforeach
                    </x-globals::forms.select>
                </x-globals::forms.form-field>

                <x-globals::forms.form-field label-text="{{ __('label.search_term') }}" name="termInput">
                    <x-globals::forms.input :bare="true" type="text" name="termInput" id="termInput"
                           style="width:100%;"
                           value="{{ $searchCriteria['term'] }}"
                           placeholder="{{ __('label.search_term') }}" />
                </x-globals::forms.form-field>

                <div style="margin-top:8px;">
                    <x-globals::forms.button submit type="primary" name="search" class="form-control">{{ __('buttons.apply') }}</x-globals::forms.button>
                </div>

            </div>
        </div>

        @if(isset($taskToggle) && $taskToggle === true)
            <div class="" style="float:right; margin-left:5px;">
                <x-globals::forms.checkbox name="showTasks" id="taskTypeToggle" value="true" label="Show Tasks"
                    :checked="$tpl->get('showTasks') === 'true'"
                    onchange="jQuery('#ticketSearch').submit();" toggle />
            </div>
        @endif

    </div>

    <div class="clearall"></div>

    @dispatchEvent('filters.beforeBar')

    @dispatchEvent('filters.beforeFormClose')
</form>

<script>
    jQuery(document).ready(function() {
        leantime.ticketsController.initTicketSearchSubmit('{{ $currentUrlPath }}');
    });
</script>
