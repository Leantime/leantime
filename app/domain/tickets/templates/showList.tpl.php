<?php

    defined('RESTRICTED') or die('Restricted access');

    $sprints        = $this->get("sprints");
    $searchCriteria = $this->get("searchCriteria");
    $currentSprint  = $this->get("currentSprint");
    $allTickets     = $this->get('allTickets');

    $todoTypeIcons  = $this->get("ticketTypeIcons");

    $efforts        = $this->get('efforts');
    $priorities     = $this->get('priorities');
    $statusLabels   = $this->get('allTicketStates');
    $groupBy        = $this->get('groupBy');
    $newField       = $this->get('newField');

    //All states >0 (<1 is archive)
    $numberofColumns = count($this->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);

?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-fw fa-thumb-tack"></span></div>
    <div class="pagetitle">
       <h5><?php $this->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName'] ?? ''); ?></h5>
        <h1><?php echo $this->__("headlines.todos"); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <form action="" method="get" id="ticketSearch">

            <?php $this->dispatchTplEvent('filters.afterFormOpen'); ?>

            <input type="hidden" value="1" name="search"/>


            <div class="simpleButtons">

                    <?php
                    $this->dispatchTplEvent('filters.afterLefthandSectionOpen');
                    if ($login::userIsAtLeast($roles::$editor) && !empty($newField)) {
                        ?>
                    <div class="btn-group pull-left">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?=$this->__("links.new_with_icon") ?> <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <?php foreach ($newField as $option) { ?>
                                <li>
                                    <a
                                        href="<?= !empty($option['url']) ? $option['url'] : '' ?>"
                                        class="<?= !empty($option['class']) ? $option['class'] : '' ?>"
                                    > <?= !empty($option['text']) ? $this->__($option['text']) : '' ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                        <?php
                    }
                    $this->dispatchTplEvent('filters.beforeLefthandSectionClose');
                    ?>



                    <?php $this->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                    <div class="right">

                        <a onclick="leantime.ticketsController.toggleFilterBar();" class="btn btn-default" data-tippy-content="<?=$this->__("popover.filter") ?>"><i class="fas fa-filter"></i><?=$this->get('numOfFilters') > 0 ? " (" . $this->get('numOfFilters') . ")" : "" ?></a>
                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="<?=$this->__("popover.group_by") ?>"><span class="fa fa-object-group"></span></button>
                            <ul class="dropdown-menu">
                                <?php foreach ($groupBy as $input) : ?>
                                    <li>
                                        <span class="radio">
                                            <input
                                                type="radio"
                                                name="groupBy"
                                                <?php if ($searchCriteria["groupBy"] == $input['status']) {
                                                    echo "checked='checked'";
                                                }?>
                                                value="<?php echo $input['status']; ?>"
                                                id="<?php echo $input['id']; ?>"
                                                onclick="jQuery('#ticketSearch').submit();"
                                            />
                                            <label for="<?php echo $input['id'] ?>"><?=$this->__("label.{$input['label']}") ?></label>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                        </div>

                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="<?=$this->__("popover.view") ?>"><i class="fa fa-list"></i></button>
                            <ul class="dropdown-menu">
                                <li><a
                                    <?php if (isset($_SESSION['lastFilterdTicketKanbanView']) && $_SESSION['lastFilterdTicketKanbanView'] != "") { ?>
                                        href="<?=$_SESSION['lastFilterdTicketKanbanView'] ?>"
                                    <?php } else { ?>
                                        href="<?=BASE_URL ?>/tickets/showKanban"
                                    <?php } ?>
                                ><?=$this->__("links.kanban") ?></a></li>
                                <li><a
                                    <?php if (isset($_SESSION['lastFilterdTicketTableView']) && $_SESSION['lastFilterdTicketTableView'] != "") { ?>
                                        href="<?=$_SESSION['lastFilterdTicketTableView'] ?>"
                                    <?php } else { ?>
                                        href="<?=BASE_URL ?>/tickets/showAll"
                                    <?php } ?>
                                ><?=$this->__("links.table") ?></a></li>
                                <li><a
                                    <?php if (isset($_SESSION['lastFilterdTicketListView']) && $_SESSION['lastFilterdTicketListView'] != "") { ?>
                                        href="<?=$_SESSION['lastFilterdTicketListView'] ?>"
                                    <?php } else { ?>
                                        href="<?=BASE_URL ?>/tickets/showList"
                                    <?php } ?>
                                    class="active"
                                ><?=$this->__("links.list_view") ?></a></li>
                            </ul>
                        </div>

                        <?php $this->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>
                    </div>



            </div>

            <div class="clearfix"></div>

            <?php $this->dispatchTplEvent('filters.beforeBar'); ?>

            <div class="filterBar hideOnLoad">

                <div class="row-fluid">

                    <?php $this->dispatchTplEvent('filters.beforeFirstBarField'); ?>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.user") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_user") ?>"  style="width:130px;" title="<?=$this->__("input.placeholders.filter_by_user") ?>" name="users" multiple="multiple" class="user-select" id="userSelect">
                                <option value=""></option>
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

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.milestone") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" title="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="milestone" id="milestoneSelect">
                                <option value=""><?=$this->__("label.all_milestones") ?></option>
                                <?php foreach ($this->get('milestones') as $milestoneRow) {   ?>
                                    <?php echo"<option value='" . $milestoneRow->id . "'";

                                    if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) {
                                        echo" selected='selected' ";
                                    }

                                    echo">" . $this->escape($milestoneRow->headline) . "</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.todo_type") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_type") ?>" title="<?=$this->__("input.placeholders.filter_by_type") ?>" name="type" id="typeSelect">
                                <option value=""><?=$this->__("label.all_types") ?></option>
                                <?php foreach ($this->get('types') as $type) {    ?>
                                    <?php echo"<option value='" . $type . "'";

                                    if (isset($searchCriteria['type']) && ($searchCriteria['type'] == $type)) {
                                        echo" selected='selected' ";
                                    }

                                    echo">$type</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.todo_priority") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_priority") ?>" title="<?=$this->__("input.placeholders.filter_by_priority") ?>" name="type" id="prioritySelect">
                                <option value=""><?=$this->__("label.all_priorities") ?></option>
                                <?php foreach ($this->get('priorities') as $priorityKey => $priorityValue) {    ?>
                                    <?php echo"<option value='" . $priorityKey . "'";

                                    if (isset($searchCriteria['priority']) && ($searchCriteria['priority'] == $priorityKey)) {
                                        echo" selected='selected' ";
                                    }

                                    echo">$priorityValue</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.todo_status") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_status")?>" name="searchStatus"  multiple="multiple" class="status-select" id="statusSelect">
                                <option value=""></option>
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

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.search_term") ?></label><br />
                        <input type="text" class="form-control input-default" id="termInput" name="term" placeholder="<?=$this->__("input.placeholders.search") ?>" value="<?php $this->e($searchCriteria['term']); ?>">
                        <input type="submit" value="<?=$this->__("buttons.search") ?>" name="search" class="form-control btn btn-primary" />
                    </div>


                </div>

            </div>

            <?php $this->dispatchTplEvent('filters.beforeFormClose'); ?>

        </form>

        <?php $this->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

        <div class="row">
            <div class="col-md-4">
                <div class="quickAddForm" style="margin-top:15px;">
                    <form action="" method="post">
                        <input type="text" name="headline" autofocus placeholder="<?php echo $this->__("input.placeholders.create_task"); ?>" style="margin-bottom: 15px; margin-top: 3px; width: 320px;"/>
                        <input type="hidden" name="sprint" value="<?=$currentSprint?>" />
                        <input type="hidden" name="quickadd" value="1"/>
                        <input type="submit" class="btn btn-default" value="<?php echo $this->__('buttons.save'); ?>" name="saveTicket" style="vertical-align: top; margin-top:3px;  width:100px;"/>
                    </form>
                    <table id="allTicketsTable" class="table display listStyleTable" style="width:100%">

                        <?php $this->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
                        <thead>
                        <?php $this->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                        <tr style="display:none;">

                            <th style="width:20px" class="status-col"><?= $this->__("label.todo_status"); ?></th>
                            <th><?= $this->__("label.title"); ?></th>

                            <th class="milestone-col"><?= $this->__("label.milestone"); ?></th>
                            <th class="priority-col"><?= $this->__("label.priority"); ?></th>
                            <th class="user-col"><?= $this->__("label.editor"); ?>.</th>
                            <th class="sprint-col"><?= $this->__("label.sprint"); ?></th>
                            <th class="tags-col"><?= $this->__("label.tags"); ?></th>
                        </tr>

                        <?php $this->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
                        </thead>

                        <?php $this->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
                        <tbody>
                        <?php $this->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                        <?php foreach ($allTickets as $rowNum => $row) {?>
                            <tr onclick="leantime.ticketsController.loadTicketToContainer('<?=$row['id']?>', '#ticketContent')" id="row-<?=$row['id']?>" class="ticketRows">
                                <?php $this->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                                <td data-order="<?=$statusLabels[$row['status']]["sortKey"]; ?>" data-search="<?=$statusLabels[$row['status']]["name"]; ?>" class="roundStatusBtn" style="width:20px">
                                    <div class="dropdown ticketDropdown statusDropdown colorized show">
                                        <a class="dropdown-toggle status <?=isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]["class"] : '' ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                            <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>
                                            <?php foreach ($statusLabels as $key => $label) {
                                                echo"<li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='" . $label["class"] . "' data-label='" . $this->escape($label["name"]) . "' data-value='" . $row['id'] . "_" . $key . "_" . $label["class"] . "' id='ticketStatusChange" . $row['id'] . $key . "' >" . $this->escape($label["name"]) . "</a>";
                                                echo"</li>";
                                            }?>
                                        </ul>
                                    </div>
                                </td>

                                <td data-search="<?=$statusLabels[$row['status']]["name"]; ?>" data-order="<?=$this->e($row['headline']); ?>" >
                                    <a href="javascript:void(0);"><strong><?=$this->e($row['headline']); ?></strong></a></td>
                                <td data-search="<?=$this->escape($row['milestoneHeadline']) ?>" data-order="<?=$this->escape($row['milestoneHeadline']) ?>"><?=$this->escape($row['milestoneHeadline']) ?></td>
                                <td data-search="<?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?>" data-order="<?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?>"><?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?></td>
                                <td data-search="<?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?>" data-order="<?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?>"><?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?></td>
                                <td data-search="<?=$this->escape($row['sprintName']); ?>"><?=$this->escape($row['sprintName']); ?></td>
                                <td data-search="<?=$row['tags'] ?>">
                                    <?php if ($row['tags'] != '') {?>
                                        <?php  $tagsArray = explode(",", $row['tags']); ?>
                                        <div class='tagsinput readonly'>
                                            <?php

                                            foreach ($tagsArray as $tag) {
                                                echo"<span class='tag'><span>" . $tag . "</span></span>";
                                            }

                                            ?>
                                        </div>
                                    <?php } ?>
                                </td>


                                <?php $this->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                            </tr>
                        <?php } ?>
                        <?php $this->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
                        </tbody>
                        <?php $this->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
                    </table>
                </div>
            </div>
            <div class="col-md-8 hidden-sm"  >
                <div id="ticketContent">
                    <div class="center">
                        <div class='svgContainer'>
                            <?=file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg"); ?>
                        </div>

                        <h3><?=$this->__("headlines.pick_a_task")?></h3>
                        <?=$this->__("text.edit_tasks_in_here"); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>


        leantime.ticketsController.initModals();


        leantime.ticketsController.initTicketSearchSubmit("<?=BASE_URL ?>/tickets/showList");


        leantime.ticketsController.initUserSelectBox();
        leantime.ticketsController.initStatusSelectBox();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initStatusDropdown();
        <?php } else { ?>
        leantime.generalController.makeInputReadonly(".maincontentinner");
        <?php } ?>



        leantime.ticketsController.initTicketsList("<?=$searchCriteria["groupBy"] ?>");

        <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

    });

</script>
