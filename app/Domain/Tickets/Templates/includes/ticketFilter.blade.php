<?php

use Leantime\Core\Controller\Frontcontroller;

$currentRoute = currentRoute();

$currentUrlPath = BASE_URL . '/' . str_replace('.', '/', $currentRoute);
$groupBy = $tpl->get('groupByOptions');
$sortBy = $tpl->get('sortOptions');
$searchCriteria = $tpl->get('searchCriteria');
$statusLabels = $tpl->get('allTicketStates');
$taskToggle = $tpl->get('enableTaskTypeToggle');

?>
<form action="" method="get" id="ticketSearch">

    <input type="hidden" value="1" name="search" />
    <input type="hidden" value="<?php echo session('currentProject'); ?>" name="projectId" id="projectIdInput" />

    <div class="filterWrapper"style="display:inline-block; position:relative; vertical-align: bottom; margin-bottom:20px;">
        <div class="filterWrapper" style="display:inline-block; position:relative; vertical-align: bottom; margin-bottom:20px;">
            <!-- Trigger for the dropdown -->
            <x-global::actions.dropdown variant="card" contentRole="ghost" cardLabel="Filter Options">
                <x-slot:labelText>
                    {{ __('popover.filter') }}
                    <span class="fa-solid fa-filter"></span>
                </x-slot:labelText>
                <x-slot:cardContent>
                    <!-- Filter Bar Content -->
                    <div style="width:250px;">
                        <div class="row-fluid">
        
                            @dispatchTplEvent('filters.beforeFirstBarField')
        
                            <div class="form-group">
                                <label class="inline">{{ __('label.user') }}</label>
                                <select data-placeholder="{{ __('input.placeholders.filter_by_user') }}" title="{{ __('input.placeholders.filter_by_user') }}" name="users" multiple="multiple" class="user-select" id="userSelect">
                                    <option value="" data-placeholder="true">All Users</option>
                                    @foreach ($tpl->get('users') as $userRow)
                                        <option value="{{ $userRow['id'] }}" 
                                            @if($searchCriteria['users'] && array_search($userRow['id'], explode(',', $searchCriteria['users'])) !== false) selected @endif>
                                            {{ sprintf(__('text.full_name'), $tpl->escape($userRow['firstname']), $tpl->escape($userRow['lastname'])) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                            <div class="form-group">
                                <label class="inline">{{ __('label.milestone') }}</label>
                                <select data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}" multiple="multiple" title="{{ __('input.placeholders.filter_by_milestone') }}" name="milestone" id="milestoneSelect">
                                    <option value="" data-placeholder="true">{{ __('label.all_milestones') }}</option>
                                    @foreach ($tpl->get('milestones') as $milestoneRow)
                                        <option value="{{ $milestoneRow->id }}" 
                                            @if(isset($searchCriteria['milestone']) && array_search($milestoneRow->id, explode(',', $searchCriteria['milestone'])) !== false) selected @endif>
                                            {{ $tpl->escape($milestoneRow->headline) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                            <div class="form-group">
                                <label class="inline">{{ __('label.todo_type') }}</label>
                                <select multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_type') }}" title="{{ __('input.placeholders.filter_by_type') }}" name="type" id="typeSelect">
                                    <option value="" data-placeholder="true">{{ __('label.all_types') }}</option>
                                    @foreach ($tpl->get('types') as $type)
                                        <option value="{{ $type }}" 
                                            @if(isset($searchCriteria['type']) && array_search($type, explode(',', $searchCriteria['type'])) !== false) selected @endif>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                            <div class="form-group">
                                <label class="inline">{{ __('label.todo_priority') }}</label>
                                <select multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_priority') }}" title="{{ __('input.placeholders.filter_by_priority') }}" name="priority" id="prioritySelect">
                                    <option value="" data-placeholder="true">{{ __('label.all_priorities') }}</option>
                                    @foreach ($tpl->get('priorities') as $priorityKey => $priorityValue)
                                        <option value="{{ $priorityKey }}" 
                                            @if(isset($searchCriteria['priority']) && array_search($priorityKey, explode(',', $searchCriteria['priority'])) !== false) selected @endif>
                                            {{ $priorityValue }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                            <div class="form-group">
                                <label class="inline">{{ __('label.todo_status') }}</label>
                                <select multiple="multiple" data-placeholder="{{ __('input.placeholders.filter_by_status') }}" name="searchStatus" class="status-select" id="statusSelect">
                                    <option value="" data-placeholder="true">All Statuses</option>
                                    <option value="not_done" @if($searchCriteria['status'] && str_contains($searchCriteria['status'], 'not_done')) selected @endif>
                                        {{ __('label.not_done') }}
                                    </option>
                                    @foreach ($statusLabels as $key => $label)
                                        <option value="{{ $key }}" 
                                            @if($searchCriteria['status'] && array_search((string) $key, explode(',', $searchCriteria['status'])) !== false) selected @endif>
                                            {{ $tpl->escape($label['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                            <div class="form-group">
                                <label class="inline">{{ __('label.search_term') }}</label>
                                <input type="text" name="termInput" id="termInput" style="width: 230px" value="{{ $searchCriteria['term'] }}" placeholder="{{ __('label.search_term') }}">
                            </div>
        
                            <div class="form-group" style="margin-top:15px;">
                                <input type="submit" value="{{ __('buttons.search') }}" name="search" class="form-control btn btn-primary" />
                            </div>
        
                        </div>
                    </div>
                </x-slot:cardContent>
            </x-global::actions.dropdown>
        </div>
        
        <?php if ($currentRoute !== 'tickets.roadmap' && $currentRoute != "tickets.showProjectCalendar") {
            ?><div class="btn-group viewDropDown">
            <x-global::actions.dropdown contentRole="ghost">
                <x-slot:labelText>
                        {{ __('popover.group_by') }}
                        <span class="fa-solid fa-diagram-project"></span>
                        @if ($searchCriteria['groupBy'] !== 'all' && $searchCriteria['groupBy'] !== '')
                            <span class="badge badge-primary">1</span>
                        @endif
                    </button>
                </x-slot:labelText>

                <x-slot:menu>
                    @foreach ($groupBy as $input)
                        <x-global::actions.dropdown.item>
                            <span class="radio">
                                <input type="radio" name="groupBy"
                                    @if ($searchCriteria['groupBy'] == $input['field']) checked='checked' @endif
                                    value="{{ $input['field'] }}" id="{{ $input['id'] }}"
                                    onclick="leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')" />
                                <label for="{{ $input['id'] }}">{{ __('label.' . $input['label']) }}</label>
                            </span>
                        </x-global::actions.dropdown.item>
                    @endforeach
                </x-slot:menu>
            </x-global::actions.dropdown>

        </div>
        <?php } ?>


        <?php if(isset($taskToggle) && $taskToggle === true){ ?>
        <div class="" style="float:right; margin-left:5px; ">
            <x-global::forms.checkbox labelText="Show Tasks" labelPosition="right" name="showTasks" value="true"
                :checked="($tpl->get('showTasks') === 'true')" id="taskTypeToggle" class="toggle" onchange="jQuery('#ticketSearch').submit();" />
        </div>
        <?php } ?>
    </div>



    <div class="clearall"></div>

    <?php $tpl->dispatchTplEvent('filters.beforeBar'); ?>



    <?php $tpl->dispatchTplEvent('filters.beforeFormClose'); ?>
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

        leantime.ticketsController.initTicketSearchSubmit('<?= $currentUrlPath ?>');

    })
</script>
