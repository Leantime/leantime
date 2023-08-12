<?php

    defined('RESTRICTED') or die('Restricted access');
    foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
    $sprints        = $tpl->get("sprints");
    $searchCriteria = $tpl->get("searchCriteria");
    $currentSprint  = $tpl->get("currentSprint");
    $allTickets     = $tpl->get('allTickets');

    $todoTypeIcons  = $tpl->get("ticketTypeIcons");

    $efforts        = $tpl->get('efforts');
    $priorities     = $tpl->get('priorities');
    $statusLabels   = $tpl->get('allTicketStates');
    $groupBy        = $tpl->get('groupBy');
    $newField       = $tpl->get('newField');

    //All states >0 (<1 is archive)
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);

?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-fw fa-thumb-tack"></span></div>
    <div class="pagetitle">
       <h5><?php $tpl->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName'] ?? ''); ?></h5>
        <h1><?php echo $tpl->__("headlines.todos"); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <form action="" method="get" id="ticketSearch">

            <?php $tpl->dispatchTplEvent('filters.afterFormOpen'); ?>

            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-5">
                    <?php
                    $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
                    if ($login::userIsAtLeast($roles::$editor) && !empty($newField)) {
                        ?>
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?=$tpl->__("links.new_with_icon") ?> <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <?php foreach ($newField as $option) { ?>
                                <li>
                                    <a
                                        href="<?= !empty($option['url']) ? $option['url'] : '' ?>"
                                        class="<?= !empty($option['class']) ? $option['class'] : '' ?>"
                                    > <?= !empty($option['text']) ? $tpl->__($option['text']) : '' ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                        <?php
                    }
                    $tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
                    ?>
                </div>

                <div class="col-md-2 center">

                    <?php $tpl->dispatchTplEvent('filters.afterCenterSectionOpen'); ?>
                    <span class="currentSprint">
                        <?php if ($tpl->get('sprints') !== false && count($tpl->get('sprints'))  > 0) {?>
                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_sprint") ?>" title="<?=$tpl->__("input.placeholders.filter_by_sprint") ?>" name="sprint" class="mainSprintSelector" onchange="form.submit()" id="sprintSelect">
                            <option value="all" <?php if ($searchCriteria['sprint'] != "all") {
                                echo"selected='selected'";
                                                } ?>><?=$tpl->__("links.all_todos") ?></option>
                            <option value="backlog" <?php if ($searchCriteria['sprint'] == "backlog") {
                                echo"selected='selected'";
                                                    } ?>><?=$tpl->__("links.backlog") ?></option>
                                <?php
                                $dates = "";
                                foreach ($tpl->get('sprints') as $sprintRow) {   ?>
                                    <?php echo"<option value='" . $sprintRow->id . "'";

                                    if ($tpl->get("currentSprint") !== false && $sprintRow->id == $tpl->get("currentSprint")) {
                                        echo " selected='selected' ";

                                        $dates = sprintf($tpl->__("label.date_from_date_to"), $tpl->getFormattedDateString($sprintRow->startDate), $tpl->getFormattedDateString($sprintRow->endDate));
                                    }
                                    echo ">";
                                    $tpl->e($sprintRow->name);
                                    echo "</option>";
                                    ?>

                                <?php }     ?>
                            </select>
                            <br/>
                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                <small>
                                <?php if ($dates != "") {
                                    echo $dates; ?> - <a href="<?=BASE_URL ?>/sprints/editSprint/<?=$tpl->get("currentSprint")?>" class="sprintModal"><?=$tpl->__("links.edit_sprint") ?></a>
                                <?php } else { ?>
                                    <a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><?=$tpl->__("links.create_sprint") ?></a>
                                <?php } ?>
                                </small>
                            <?php } ?>
                        <?php } ?>
                    </span>
                    <?php $tpl->dispatchTplEvent('filters.beforeCenterSectionClose'); ?>
                </div>
                <div class="col-md-5">
                    <div class="pull-right">

                        <?php $tpl->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                        <div id="tableButtons" style="display:inline-block"></div>
                        <a onclick="leantime.ticketsController.toggleFilterBar();" class="btn btn-default" data-tippy-content="<?=$tpl->__("popover.filter") ?>"><i class="fas fa-filter"></i><?=$tpl->get('numOfFilters') > 0 ? " (" . $tpl->get('numOfFilters') . ")" : "" ?></a>
                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="<?=$tpl->__("popover.group_by") ?>"><span class="fa fa-object-group"></span></button>
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
                                            <label for="<?php echo $input['id'] ?>"><?=$tpl->__("label.{$input['label']}") ?></label>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                        </div>

                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="<?=$tpl->__("popover.view") ?>"><i class="fa fa-table"></i></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a
                                        <?php if (isset($_SESSION['lastFilterdTicketKanbanView']) && $_SESSION['lastFilterdTicketKanbanView'] != "") { ?>
                                            href="<?= $_SESSION['lastFilterdTicketKanbanView'] ?>"
                                        <?php } else { ?>
                                            href="<?= BASE_URL ?>/tickets/showKanban"
                                        <?php } ?>
                                    ><?=$tpl->__("links.kanban") ?></a>
                                </li>
                                <li>
                                    <a
                                        <?php if (isset($_SESSION['lastFilterdTicketTableView']) && $_SESSION['lastFilterdTicketTableView'] != "") { ?>
                                            href="<?= $_SESSION['lastFilterdTicketTableView'] ?>"
                                        <?php } else { ?>
                                            href="<?= BASE_URL ?>/tickets/showAll"
                                        <?php } ?>
                                    ><?=$tpl->__("links.table") ?></a>
                                </li>
                                <li>
                                    <a
                                        <?php if (isset($_SESSION['lastFilterdTicketListView']) && $_SESSION['lastFilterdTicketListView'] != "") { ?>
                                            href="<?= $_SESSION['lastFilterdTicketListView'] ?>"
                                        <?php } else { ?>
                                            href="<?= BASE_URL ?>/tickets/showList"
                                        <?php } ?>
                                    ><?=$tpl->__("links.list_view") ?></a>
                                </li>
                            </ul>
                        </div>

                        <?php $tpl->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>

                    </div>
                </div>

            </div>

            <div class="clearfix"></div>

            <?php $tpl->dispatchTplEvent('filters.beforeBar'); ?>

            <div class="filterBar hideOnLoad">

                <div class="row-fluid">

                    <?php $tpl->dispatchTplEvent('filters.beforeFirstBarField'); ?>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$tpl->__("label.user") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_user") ?>"  style="width:130px;" title="<?=$tpl->__("input.placeholders.filter_by_user") ?>" name="users" multiple="multiple" class="user-select" id="userSelect">
                                <option value=""></option>
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

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$tpl->__("label.milestone") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_milestone") ?>" title="<?=$tpl->__("input.placeholders.filter_by_milestone") ?>" name="milestone" id="milestoneSelect">
                                <option value=""><?=$tpl->__("label.all_milestones") ?></option>
                                <?php foreach ($tpl->get('milestones') as $milestoneRow) {   ?>
                                    <?php echo"<option value='" . $milestoneRow->id . "'";

                                    if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) {
                                        echo" selected='selected' ";
                                    }

                                    echo">" . $tpl->escape($milestoneRow->headline) . "</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$tpl->__("label.todo_type") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_type") ?>" title="<?=$tpl->__("input.placeholders.filter_by_type") ?>" name="type" id="typeSelect">
                                <option value=""><?=$tpl->__("label.all_types") ?></option>
                                <?php foreach ($tpl->get('types') as $type) {    ?>
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
                        <label class="inline"><?=$tpl->__("label.todo_priority") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_priority") ?>" title="<?=$tpl->__("input.placeholders.filter_by_priority") ?>" name="type" id="prioritySelect">
                                <option value=""><?=$tpl->__("label.all_priorities") ?></option>
                                <?php foreach ($tpl->get('priorities') as $priorityKey => $priorityValue) {    ?>
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
                        <label class="inline"><?=$tpl->__("label.todo_status") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_status")?>" name="searchStatus"  multiple="multiple" class="status-select" id="statusSelect">
                                <option value=""></option>
                                <option value="not_done" <?php if ($searchCriteria['status'] !== false && strpos($searchCriteria['status'], 'not_done') !== false) {
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

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$tpl->__("label.search_term") ?></label><br />
                        <input type="text" class="form-control input-default" id="termInput" name="term" placeholder="<?=$tpl->__("input.placeholders.search") ?>" value="<?php $tpl->e($searchCriteria['term']); ?>">
                        <input type="submit" value="<?=$tpl->__("buttons.search") ?>" name="search" class="form-control btn btn-primary" />
                    </div>


                </div>

            </div>

            <?php $tpl->dispatchTplEvent('filters.beforeFormClose'); ?>

        </form>

        <?php $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

        <table id="allTicketsTable" class="table table-bordered display" style="width:100%">
            <colgroup>
                <col class="con1">
                <col class="con0" style="max-width:200px;">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
            </colgroup>
            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
            <thead>
                <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                <tr>
                    <th class="id-col"><?= $tpl->__("label.id"); ?></th>
                    <th style="max-width: 350px;"><?= $tpl->__("label.title"); ?></th>
                    <th class="status-col"><?= $tpl->__("label.todo_status"); ?></th>
                    <th class="milestone-col"><?= $tpl->__("label.milestone"); ?></th>
                    <th class="effort-col"><?= $tpl->__("label.effort"); ?></th>
                    <th class="priority-col"><?= $tpl->__("label.priority"); ?></th>
                    <th class="user-col"><?= $tpl->__("label.editor"); ?>.</th>
                    <th class="sprint-col"><?= $tpl->__("label.sprint"); ?></th>
                    <th class="tags-col"><?= $tpl->__("label.tags"); ?></th>
                    <th class="duedate-col"><?= $tpl->__("label.due_date"); ?></th>
                    <th class="planned-hours-col"><?= $tpl->__("label.planned_hours"); ?></th>
                    <th class="remaining-hours-col"><?= $tpl->__("label.estimated_hours_remaining"); ?></th>
                    <th class="booked-hours-col"><?= $tpl->__("label.booked_hours"); ?></th>
                    <th class="no-sort"></th>
                </tr>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
            </thead>
            <?php $tpl->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
            <tbody>
                <?php $tpl->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                <?php foreach ($allTickets as $rowNum => $row) {?>
                    <tr style="height:1px;">
                        <?php $tpl->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                        <td data-order="<?=$tpl->e($row['id']); ?>">
                            #<?=$tpl->e($row['id']); ?>
                        </td>

                        <td data-order="<?=$tpl->e($row['headline']); ?>">
                            <?php if ($row['dependingTicketId'] > 0) { ?>
                                <small><a href="<?=$_SESSION['lastPage'] ?>/#/tickets/showTicket/<?=$row['dependingTicketId'] ?>"><?=$tpl->escape($row['parentHeadline']) ?></a></small> //<br />
                            <?php } ?>
                            <a class='ticketModal' href="<?=BASE_URL ?>/tickets/showTicket/<?=$tpl->e($row['id']); ?>"><?=$tpl->e($row['headline']); ?></a></td>



                        <?php

                        if (isset($statusLabels[$row['status']])) {
                            $class = $statusLabels[$row['status']]["class"];
                            $name = $statusLabels[$row['status']]["name"];
                            $sortKey = $statusLabels[$row['status']]["sortKey"];
                        } else {
                            $class = 'label-important';
                            $name = 'new';
                            $sortKey = 0;
                        }

                        ?>
                        <td class="dropdown-cell" data-order="<?=$name ?>">
                            <div class="dropdown ticketDropdown statusDropdown colorized show">
                                <a class="dropdown-toggle status <?=$class ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">


                                    <span class="text">
                                        <?php

                                            echo $name;

                                        ?>

                                    </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_status")?></li>
                                    <?php foreach ($statusLabels as $key => $label) {
                                        echo"<li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='" . $label["class"] . "' data-label='" . $tpl->escape($label["name"]) . "' data-value='" . $row['id'] . "_" . $key . "_" . $label["class"] . "' id='ticketStatusChange" . $row['id'] . $key . "' >" . $tpl->escape($label["name"]) . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>



                        <?php
                        if ($row['milestoneid'] != "" && $row['milestoneid'] != 0) {
                            $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                        } else {
                            $milestoneHeadline = $tpl->__("label.no_milestone");
                        }?>

                        <td class="dropdown-cell" data-order="<?=$milestoneHeadline?>">
                            <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                <a style="background-color:<?=$tpl->escape($row['milestoneColor'])?>" class="dropdown-toggle label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?=$milestoneHeadline?></span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_milestone")?></li>
                                    <li class='dropdown-item'><a style='background-color:#b0b0b0' href='javascript:void(0);' data-label="<?=$tpl->__("label.no_milestone")?>" data-value='<?=$row['id'] . "_0_#b0b0b0"?>'> <?=$tpl->__("label.no_milestone")?> </a></li>

                                    <?php foreach ($tpl->get('milestones') as $milestone) {
                                        echo"<li class='dropdown-item'>
                                            <a href='javascript:void(0);' data-label='" . $tpl->escape($milestone->headline) . "' data-value='" . $row['id'] . "_" . $milestone->id . "_" . $tpl->escape($milestone->tags) . "' id='ticketMilestoneChange" . $row['id'] . $milestone->id . "' style='background-color:" . $tpl->escape($milestone->tags) . "'>" . $tpl->escape($milestone->headline) . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <td class="dropdown-cell"  data-order="<?=$row['storypoints'] ? $efforts[$row['storypoints']] : $tpl->__("label.story_points_unkown"); ?>">
                            <div class="dropdown ticketDropdown effortDropdown show">
                                <a class="dropdown-toggle label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?php
                                                            if ($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                                echo $efforts[$row['storypoints']];
                                                            } else {
                                                                echo $tpl->__("label.story_points_unkown");
                                                            }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$tpl->__("dropdown.how_big_todo")?></li>
                                    <?php foreach ($efforts as $effortKey => $effortValue) {
                                        echo"<li class='dropdown-item'>
                                                                        <a href='javascript:void(0);' data-value='" . $row['id'] . "_" . $effortKey . "' id='ticketEffortChange" . $row['id'] . $effortKey . "'>" . $effortValue . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>

                        <td class="dropdown-cell"  data-order="<?php
                        if ($row['priority'] != '' && $row['priority'] > 0) {
                            echo $priorities[$row['priority']];
                        } else {
                            echo $tpl->__("label.priority_unkown");
                        }?>">
                            <div class="dropdown ticketDropdown priorityDropdown show">
                                <a class="dropdown-toggle label-default priority priority-bg-<?=$row['priority']?>" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?php
                                                            if ($row['priority'] != '' && $row['priority'] > 0) {
                                                                echo $priorities[$row['priority']];
                                                            } else {
                                                                echo $tpl->__("label.priority_unkown");
                                                            }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="priorityDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$tpl->__("dropdown.select_priority")?></li>
                                    <?php foreach ($priorities as $priorityKey => $priorityValue) {
                                        echo"<li class='dropdown-item'>
                                             <a href='javascript:void(0);' class='priority-bg-" . $priorityKey . "' data-value='" . $row['id'] . "_" . $priorityKey . "' id='ticketPriorityChange" . $row['id'] . $priorityKey . "'>" . $priorityValue . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <td class="dropdown-cell"  data-order="<?=$row["editorFirstname"] != "" ?  $tpl->escape($row["editorFirstname"]) : $tpl->__("dropdown.not_assigned")?>">
                            <div class="dropdown ticketDropdown userDropdown noBg show ">
                                <a class="dropdown-toggle" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                <?php if ($row["editorFirstname"] != "") {
                                                                    echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=" . $row['editorId'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $tpl->escape($row["editorFirstname"]) . "</span>";
                                                                } else {
                                                                    echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $tpl->__("dropdown.not_assigned") . "</span>";
                                                                }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_user")?></li>

                                    <?php foreach ($tpl->get('users') as $user) {
                                        echo "<li class='dropdown-item'>";
                                        echo "<a href='javascript:void(0);' data-label='" . sprintf($tpl->__("text.full_name"), $tpl->escape($user["firstname"]), $tpl->escape($user['lastname'])) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" . $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" . BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/>" . sprintf($tpl->__("text.full_name"), $tpl->escape($user["firstname"]), $tpl->escape($user['lastname'])) . "</a>";
                                        echo "</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <?php

                        if ($row['sprint'] != "" && $row['sprint'] != 0  && $row['sprint'] != -1) {
                            $sprintHeadline = $tpl->escape($row['sprintName']);
                        } else {
                            $sprintHeadline = $tpl->__("label.backlog");
                        }?>

                        <td class="dropdown-cell"  data-order="<?=$sprintHeadline?>">

                            <div class="dropdown ticketDropdown sprintDropdown show">
                                <a class="dropdown-toggle label-default sprint" href="javascript:void(0);" role="button" id="sprintDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="text"><?=$sprintHeadline?></span>
                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="sprintDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_sprint")?></li>
                                    <li class='dropdown-item'><a href='javascript:void(0);' data-label="<?=$tpl->__("label.backlog")?>" data-value='<?=$row['id'] . "_0"?>'> <?=$tpl->__("label.backlog")?> </a></li>
                                    <?php if ($tpl->get('sprints')) {
                                        foreach ($tpl->get('sprints') as $sprint) {
                                            echo "<li class='dropdown-item'>
                                                    <a href='javascript:void(0);' data-label='" . $tpl->escape($sprint->name) . "' data-value='" . $row['id'] . "_" . $sprint->id . "' id='ticketSprintChange" . $row['id'] . $sprint->id . "' >" . $tpl->escape($sprint->name) . "</a>";
                                            echo "</li>";
                                        }
                                    }?>
                                </ul>
                            </div>
                        </td>

                        <td data-order="<?=$row['tags'] ?>">
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

                        <?php
                        if ($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                            $date = $tpl->__("text.anytime");
                        } else {
                            $date = new DateTime($row['dateToFinish']);
                            $date = $date->format($tpl->__("language.dateformat"));
                        }
                        ?>
                        <td data-order="<?=$row['dateToFinish'] ?>" >
                            <input type="text" title="<?php echo $tpl->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                        </td>
                        <td data-order="<?=$tpl->e($row['planHours']); ?>">
                            <input type="text" value="<?=$tpl->e($row['planHours']); ?>" name="planHours" class="small-input secretInput" onchange="leantime.ticketsController.updatePlannedHours(this, '<?=$row['id']?>'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                        </td>
                        <td data-order="<?=$tpl->e($row['hourRemaining']); ?>">
                            <input type="text" value="<?=$tpl->e($row['hourRemaining']); ?>" name="remainingHours" class="small-input secretInput" onchange="leantime.ticketsController.updateRemainingHours(this, '<?=$row['id']?>');" />
                        </td>

                        <td data-order="<?php if ($row['bookedHours'] === null || $row['bookedHours'] == "") {
                            echo "0";
                                        } else {
                                            echo $row['bookedHours'];
                                        }?>">

                            <?php if ($row['bookedHours'] === null || $row['bookedHours'] == "") {
                                echo "0";
                            } else {
                                echo $row['bookedHours'];
                            }?>
                        </td>
                        <td>
                            <?php if ($login::userIsAtLeast($roles::$editor)) {
                                $clockedIn = $tpl->get("onTheClock");

                                ?>
                                <div class="inlineDropDownContainer">

                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="nav-header"><?php echo $tpl->__("subtitles.todo"); ?></li>
                                        <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"]; ?>" class='ticketModal'><i class="fa fa-edit"></i> <?php echo $tpl->__("links.edit_todo"); ?></a></li>
                                        <li><a href="<?=BASE_URL ?>/tickets/moveTicket/<?php echo $row["id"]; ?>" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $tpl->__("links.move_todo"); ?></a></li>
                                        <li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__("links.delete_todo"); ?></a></li>
                                        <li class="nav-header border"><?php echo $tpl->__("subtitles.track_time"); ?></li>
                                        <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                            <a
                                                class="punchIn"
                                                href="javascript:void(0);"
                                                data-value="<?php echo $row["id"]; ?>"
                                                <?php if ($clockedIn !== false) {
                                                    echo"style='display:none;'";
                                                } ?>
                                            ><span class="fa-regular fa-clock"></span> <?php echo $tpl->__("links.start_work"); ?></a>
                                            <a
                                                class="punchOut"
                                                href="javascript:void(0);"
                                                data-value="<?php echo $row["id"]; ?>"
                                                <?php if ($clockedIn === false || $clockedIn["id"] != $row["id"]) {
                                                    echo"style='display:none;'";
                                                }?>
                                            >
                                                <span class="fa-stop"></span>
                                                <?php if (is_array($clockedIn) == true) {
                                                    echo sprintf($tpl->__("links.stop_work_started_at"), date($tpl->__("language.timeformat"), $clockedIn["since"]));
                                                } else {
                                                    echo sprintf($tpl->__("links.stop_work_started_at"), date($tpl->__("language.timeformat"), time()));
                                                } ?>
                                            </a>
                                            <span
                                                class='working'
                                                <?php if ($clockedIn === false || $clockedIn["id"] === $row["id"]) {
                                                    echo"style='display:none;'";
                                                } ?>
                                            >
                                                <?php echo $tpl->__("text.timer_set_other_todo"); ?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            <?php } ?>

                        </td>
                        <?php $tpl->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                    </tr>
                <?php } ?>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
            </tbody>
            <?php $tpl->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
        </table>
        <?php $tpl->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>


        leantime.ticketsController.initModals();


        leantime.ticketsController.initTicketSearchSubmit("<?=BASE_URL ?>/tickets/showAll");


        leantime.ticketsController.initUserSelectBox();
        leantime.ticketsController.initStatusSelectBox();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initUserDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initPriorityDropdown();
            leantime.ticketsController.initSprintDropdown();
            leantime.ticketsController.initStatusDropdown();
        <?php } else { ?>
        leantime.generalController.makeInputReadonly(".maincontentinner");
        <?php } ?>



        leantime.ticketsController.initTicketsTable("<?=$searchCriteria["groupBy"] ?>");

        <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

    });

</script>
