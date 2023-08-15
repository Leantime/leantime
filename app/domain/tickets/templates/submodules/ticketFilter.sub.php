
<?php

$currentUrlPath = BASE_URL . "/". str_replace(".", "/", \leantime\core\frontcontroller::getCurrentRoute());
$groupBy        = $this->get('groupByOptions');
$sortBy        = $this->get('sortOptions');
$searchCriteria = $this->get("searchCriteria");

?>
<form action="" method="get" id="ticketSearch">

    <input type="hidden" value="1" name="search"/>
    <input type="hidden" value="<?php echo $_SESSION['currentProject']; ?>" name="projectId" id="projectIdInput"/>

    <div class="filterWrapper" style="display:inline-block; position:relative; vertical-align: bottom;">
        <a onclick="leantime.ticketsController.toggleFilterBar();" style="margin-right:5px;"
           class="btn btn-link" data-tippy-content="<?=$this->__("popover.filter") ?>">
            <i class="fas fa-filter"></i> Filter <?=$this->get('numOfFilters') > 0 ? " (" . $this->get('numOfFilters') . ")" : "" ?>
        </a>
        <div class="filterBar hideOnLoad" style="width:250px;">

            <div class="row-fluid">

                <?php $this->dispatchTplEvent('filters.beforeFirstBarField'); ?>

                <div class="">
                    <label class="inline"><?=$this->__("label.user") ?></label>
                    <div class="form-group">
                        <select data-placeholder="<?=$this->__("input.placeholders.filter_by_user") ?>"  title="<?=$this->__("input.placeholders.filter_by_user") ?>" name="users" multiple="multiple" class="user-select" id="userSelect">
                            <option value="" data-placeholder="true">All Users</option>
                            <?php foreach ($this->get('users') as $userRow) {     ?>
                                <?php echo"<option value='" . $userRow["id"] . "'";

                                if ($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && array_search($userRow["id"], explode(",", $searchCriteria['users'])) !== false) {
                                    echo" selected='selected' ";
                                }

                                echo">" . sprintf($this->__('text.full_name'), $this->escape($userRow['firstname']), $this->escape($userRow['lastname'])) . "</option>"; ?>

                            <?php }     ?>
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline"><?=$this->__("label.milestone") ?></label>
                    <div class="form-group">
                        <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" multiple="multiple" title="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="milestone" id="milestoneSelect">
                            <option value="" data-placeholder="true"><?=$this->__("label.all_milestones") ?></option>
                            <?php foreach ($this->get('milestones') as $milestoneRow) {   ?>
                                <?php echo"<option value='" . $milestoneRow->id . "'";

                                if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id) && array_search($milestoneRow->id, explode(",", $searchCriteria['milestone'])) !== false) {
                                        echo" selected='selected' ";
                                }

                                echo">" . $this->escape($milestoneRow->headline) . "</option>"; ?>

                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="">
                    <label class="inline"><?=$this->__("label.todo_type") ?></label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="<?=$this->__("input.placeholders.filter_by_type") ?>" title="<?=$this->__("input.placeholders.filter_by_type") ?>" name="type" id="typeSelect">
                            <option value="" data-placeholder="true"><?=$this->__("label.all_types") ?></option>
                            <?php foreach ($this->get('types') as $type) {    ?>
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
                    <label class="inline"><?=$this->__("label.todo_priority") ?></label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="<?=$this->__("input.placeholders.filter_by_priority") ?>" title="<?=$this->__("input.placeholders.filter_by_priority") ?>" name="priority" id="prioritySelect">
                            <option value="" data-placeholder="true"><?=$this->__("label.all_priorities") ?></option>
                            <?php foreach ($this->get('priorities') as $priorityKey => $priorityValue) {    ?>
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
                    <label class="inline"><?=$this->__("label.todo_status") ?></label>
                    <div class="form-group">
                        <select multiple="multiple"  data-placeholder="<?=$this->__("input.placeholders.filter_by_status")?>" name="searchStatus"  multiple="multiple" class="status-select" id="statusSelect">
                            <option value="" data-placeholder="true">All Statuses</option>
                            <option value="not_done" <?php if ($searchCriteria['status'] !== false && strpos($searchCriteria['status'], 'not_done') !== false) {
                                echo" selected='selected' ";
                            }?>><?=$this->__("label.not_done")?></option>
                            <?php foreach ($statusLabels as $key => $label) {?>
                                <?php echo"<option value='" . $key . "'";

                                if ($searchCriteria['status'] !== false && array_search((string) $key, explode(",", $searchCriteria['status'])) !== false) {
                                    echo" selected='selected' ";
                                }
                                echo">" . $this->escape($label["name"]) . "</option>"; ?>

                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="" style="margin-top:15px;">
                    <input type="submit" value="<?=$this->__("buttons.search") ?>" name="search" class="form-control btn btn-primary" />
                </div>

            </div>

        </div>
    </div><div class="btn-group viewDropDown" >
        <button class="btn btn-link dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="<?=$this->__("popover.group_by") ?>"><span class="fa-solid fa-diagram-project"></span> Group By</button>
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
                        <label for="<?php echo $input['id'] ?>"><?=$this->__("label.{$input['label']}") ?></label>
                    </span>
                </li>
            <?php }; ?>
        </ul>

    </div>

    <?php $this->dispatchTplEvent('filters.beforeBar'); ?>



    <?php $this->dispatchTplEvent('filters.beforeFormClose'); ?>
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
