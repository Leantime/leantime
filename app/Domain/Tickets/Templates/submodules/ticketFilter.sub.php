
<?php

use Leantime\Core\Controller\Frontcontroller;

$currentRoute = Frontcontroller::getCurrentRoute();

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
                    <label class="inline"><?=$tpl->__("label.user") ?></label>
                    <div class="form-group">
                        <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_user") ?>"  title="<?=$tpl->__("input.placeholders.filter_by_user") ?>" name="users" multiple="multiple" class="user-select" id="userSelect">
                            <option value="" data-placeholder="true">All Users</option>
                            <?php foreach ($tpl->get('users') as $userRow) {     ?>
                                <?php echo"<option value='" . $userRow["id"] . "'";

                                if ($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && array_search($userRow["id"], explode(",", $searchCriteria['users'])) !== false) {
                                    echo" selected='selected' ";
                                }

                                echo">" . sprintf($tpl->__('text.full_name'), $tpl->escape($userRow['firstname']), $tpl->escape($userRow['lastname'])) . "</option>"; ?>

                            <?php }     ?>
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline"><?=$tpl->__("label.milestone") ?></label>
                    <div class="form-group">
                        <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_milestone") ?>" multiple="multiple" title="<?=$tpl->__("input.placeholders.filter_by_milestone") ?>" name="milestone" id="milestoneSelect">
                            <option value="" data-placeholder="true"><?=$tpl->__("label.all_milestones") ?></option>
                            <?php
                            if (is_array($tpl->get('milestones'))) {
                                foreach ($tpl->get('milestones') as $milestoneRow) {   ?>
                                    <?php echo"<option value='" . $milestoneRow->id . "'";

                                    if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id) && array_search($milestoneRow->id, explode(",", $searchCriteria['milestone'])) !== false) {
                                        echo" selected='selected' ";
                                    }

                                    echo">" . $tpl->escape($milestoneRow->headline) . "</option>"; ?>

                                <?php }
                            }?>
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline"><?=$tpl->__("label.todo_type") ?></label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="<?=$tpl->__("input.placeholders.filter_by_type") ?>" title="<?=$tpl->__("input.placeholders.filter_by_type") ?>" name="type" id="typeSelect">
                            <option value="" data-placeholder="true"><?=$tpl->__("label.all_types") ?></option>
                            <?php foreach ($tpl->get('types') as $type) {    ?>
                                <?php echo"<option value='" . $type . "'";

                                if (isset($searchCriteria['type']) && array_search($type, explode(",", $searchCriteria['type'])) !== false) {
                                    echo" selected='selected' ";
                                }

                                echo">$type</option>"; ?>

                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline"><?=$tpl->__("label.todo_priority") ?></label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="<?=$tpl->__("input.placeholders.filter_by_priority") ?>" title="<?=$tpl->__("input.placeholders.filter_by_priority") ?>" name="priority" id="prioritySelect">
                            <option value="" data-placeholder="true"><?=$tpl->__("label.all_priorities") ?></option>
                            <?php foreach ($tpl->get('priorities') as $priorityKey => $priorityValue) {    ?>
                                <?php echo"<option value='" . $priorityKey . "'";

                                if (isset($searchCriteria['priority']) && array_search($priorityKey, explode(",", $searchCriteria['priority'])) !== false) {
                                    echo" selected='selected' ";
                                }

                                echo">$priorityValue</option>"; ?>

                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline"><?=$tpl->__("label.todo_status") ?></label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="<?=$tpl->__("input.placeholders.filter_by_status")?>" name="searchStatus"  multiple="multiple" class="status-select" id="statusSelect">
                            <option value="" data-placeholder="true">All Statuses</option>
                            <option value="not_done" <?php if (
                            $searchCriteria['status'] !== false && str_contains(
                                $searchCriteria['status'],
                                'not_done'
                            )
) {
                                                                           echo" selected='selected' ";
                                                     }?>><?=$tpl->__("label.not_done")?></option>
                            <?php foreach ($statusLabels as $key => $label) {?>
                                <?php echo"<option value='" . $key . "'";

                                if ($searchCriteria['status'] !== false && array_search((string) $key, explode(",", $searchCriteria['status'])) !== false) {
                                    echo" selected='selected' ";
                                }
                                echo">" . $tpl->escape($label["name"]) . "</option>"; ?>

                            <?php } ?>
                        </select>
                    </div>
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
                <input type="checkbox" class="toggle" id="taskTypeToggle" onchange="jQuery('#ticketSearch').submit();" name="showTasks" value="true" <?=($tpl->get('showTasks') === "true") ? 'checked="checked"' : ''; ?> style="margin-right:5px;" />
                <label style="text-wrap: nowrap; float:right;">Show Tasks</label>
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
