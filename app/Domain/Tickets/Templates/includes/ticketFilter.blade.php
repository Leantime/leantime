
<?php

use Leantime\Core\Controller\Frontcontroller;

$currentRoute = currentRoute();

$currentUrlPath = BASE_URL . "/" . str_replace(".", "/", $currentRoute);
$groupBy        = $tpl->get('groupByOptions');
$sortBy        = $tpl->get('sortOptions');
$searchCriteria = $tpl->get("searchCriteria");
$statusLabels = $tpl->get("allTicketStates");
$taskToggle = $tpl->get("enableTaskTypeToggle");

?>
<form action="" method="get" id="ticketSearch">

    <input type="hidden" value="1" name="search"/>
    <input type="hidden" value="<?php echo session("currentProject"); ?>" name="projectId" id="projectIdInput"/>

    <div class="filterWrapper" style="display:inline-block; position:relative; vertical-align: bottom; margin-bottom:20px;">
        <a onclick="leantime.ticketsController.toggleFilterBar();" style="margin-right:5px;"
           class="btn btn-link" data-tippy-content="<?=$tpl->__("popover.filter") ?>">
            <i class="fas fa-filter"></i> Filter<?=$tpl->get('numOfFilters') > 0 ? "  <span class='badge badge-primary'>" . $tpl->get('numOfFilters') . "</span> " : "" ?>
            <?php /*Please don't change the code formatting below, if not right next to each other it somehow adds a space between the two buttons and increases the distance */ ?>
        </a><?php if ($currentRoute !== 'tickets.roadmap' && $currentRoute != "tickets.showProjectCalendar") {
            ?><div class="btn-group viewDropDown">
            <button class="btn btn-link dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="<?=$tpl->__("popover.group_by") ?>">
                <span class="fa-solid fa-diagram-project"></span> Group By
                <?php if ($searchCriteria["groupBy"] != 'all' && $searchCriteria["groupBy"] != '') { ?>
                    <span class="badge badge-primary">1</span>
                <?php } ?>
            </button>
            <ul class="dropdown-menu">
                <?php foreach ($groupBy as $input) { ?>
                    <li>
                        <span class="radio">
                            <input
                                type="radio"
                                name="groupBy"
                                <?php if ($searchCriteria["groupBy"] == $input['field']) {
                                    echo "checked='checked'";
                                }?>
                                value="<?php echo $input['field']; ?>"
                                id="<?php echo $input['id']; ?>"
                                onclick="leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"
                            />
                            <label for="<?php echo $input['id'] ?>"><?=$tpl->__("label.{$input['label']}") ?></label>
                        </span>
                    </li>
                <?php }; ?>
            </ul>
        </div>
            <?php } ?>
        <div class="filterBar hideOnLoad" style="width:250px;">

            <div class="row-fluid">

                <?php $tpl->dispatchTplEvent('filters.beforeFirstBarField'); ?>

                <div class="">
                    <x-global::forms.select 
                    name="users" 
                    id="userSelect" 
                    class="user-select" 
                    multiple="multiple" 
                    :placeholder="__('input.placeholders.filter_by_user')" 
                    labelText="{!! __('label.user') !!}" 
                    title="{!! __('input.placeholders.filter_by_user') !!}"
                >
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {!! __('All Users') !!}
                    </x-global::forms.select.select-option>
                
                    @foreach ($tpl->get('users') as $userRow)
                        <x-global::forms.select.select-option 
                            value="{{ $userRow['id'] }}" 
                            :selected="($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && in_array($userRow['id'], explode(',', $searchCriteria['users'])))">
                            {!! sprintf(__('text.full_name'), $tpl->escape($userRow['firstname']), $tpl->escape($userRow['lastname'])) !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                </div>

                <div class="">
                    <x-global::forms.select 
                    name="milestone" 
                    id="milestoneSelect" 
                    multiple="multiple" 
                    :placeholder="__('input.placeholders.filter_by_milestone')" 
                    labelText="{!! __('label.milestone') !!}" 
                    title="{!! __('input.placeholders.filter_by_milestone') !!}"
                >
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {!! __('label.all_milestones') !!}
                    </x-global::forms.select.select-option>
                
                    @if (is_array($tpl->get('milestones')))
                        @foreach ($tpl->get('milestones') as $milestoneRow)
                            <x-global::forms.select.select-option 
                                value="{{ $milestoneRow->id }}" 
                                :selected="isset($searchCriteria['milestone']) && array_search($milestoneRow->id, explode(',', $searchCriteria['milestone'])) !== false">
                                {!! $tpl->escape($milestoneRow->headline) !!}
                            </x-global::forms.select.select-option>
                        @endforeach
                    @endif
                </x-global::forms.select>
                
                </div>

                <div class="">
                    <x-global::forms.select 
                    name="type" 
                    id="typeSelect" 
                    multiple="multiple" 
                    :placeholder="__('input.placeholders.filter_by_type')" 
                    labelText="{!! __('label.todo_type') !!}" 
                    title="{!! __('input.placeholders.filter_by_type') !!}"
                >
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {!! __('label.all_types') !!}
                    </x-global::forms.select.select-option>
                
                    @foreach ($tpl->get('types') as $type)
                        <x-global::forms.select.select-option 
                            value="{{ $type }}" 
                            :selected="isset($searchCriteria['type']) && array_search($type, explode(',', $searchCriteria['type'])) !== false">
                            {!! $type !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                </div>

                <div class="">
                    <x-global::forms.select 
                    name="priority" 
                    id="prioritySelect" 
                    variant="multiple" 
                    :placeholder="__('input.placeholders.filter_by_priority')" 
                    labelText="{!! __('label.todo_priority') !!}" 
                    title="{!! __('input.placeholders.filter_by_priority') !!}"
                >
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {!! __('label.all_priorities') !!}
                    </x-global::forms.select.select-option>
                
                    @foreach ($tpl->get('priorities') as $priorityKey => $priorityValue)
                        <x-global::forms.select.select-option 
                            value="{{ $priorityKey }}" 
                            :selected="isset($searchCriteria['priority']) && array_search($priorityKey, explode(',', $searchCriteria['priority'])) !== false">
                            {!! $priorityValue !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                </div>

                <div class="">
                    <x-global::forms.select 
                    name="searchStatus" 
                    id="statusSelect" 
                    class="status-select" 
                    multiple="multiple" 
                    :placeholder="__('input.placeholders.filter_by_status')" 
                    labelText="{!! __('label.todo_status') !!}" 
                    variant="multiple"
                >
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {!! __('All Statuses') !!}
                    </x-global::forms.select.select-option>
                
                    <x-global::forms.select.select-option 
                        value="not_done" 
                        :selected="isset($searchCriteria['status']) && str_contains($searchCriteria['status'], 'not_done')">
                        {!! __('label.not_done') !!}
                    </x-global::forms.select.select-option>
                
                    @foreach ($statusLabels as $key => $label)
                        <x-global::forms.select.select-option 
                            value="{{ $key }}" 
                            :selected="isset($searchCriteria['status']) && array_search((string)$key, explode(',', $searchCriteria['status'])) !== false">
                            {!! $tpl->escape($label['name']) !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                </div>

                <div class="">
                    <div class="form-group">
                        <label class="inline"><?=$tpl->__("label.search_term")?></label>
                        <input type="text" name="termInput" id="termInput"
                        style="width: 230px"
                        value="<?=$searchCriteria['term']?>"
                        placeholder="<?=$tpl->__("label.search_term")?>">
                    </div>
                </div>

                <div class="" style="margin-top:15px;">
                    <input type="submit" value="<?=$tpl->__("buttons.search") ?>" name="search" class="form-control btn btn-primary" />
                </div>

            </div>

        </div>

        <?php if(isset($taskToggle) && $taskToggle === true){ ?>
            <div class="" style="float:right; margin-left:5px; ">
                <x-global::forms.checkbox
                    labelText="Show Tasks"
                    labelPosition="right"
                    name="showTasks"
                    value="true"
                    :checked="($tpl->get('showTasks') === 'true')"
                    id="taskTypeToggle"
                    class="toggle"
                    onchange="jQuery('#ticketSearch').submit();"
                />
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

        leantime.ticketsController.initTicketSearchSubmit('<?=$currentUrlPath; ?>');

    })
</script>
