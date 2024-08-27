<?php

    defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
    $sprints        = $tpl->get("sprints");
    $searchCriteria = $tpl->get("searchCriteria");
    $currentSprint  = $tpl->get("currentSprint");
    $allTickets     = $tpl->get('allTickets');

    echo $tpl->displayNotification();

    $searchCriteria = $tpl->get("searchCriteria");
    $currentSprint  = $tpl->get("currentSprint");
    $allTicketGroups     = $tpl->get('allTickets');

    $todoTypeIcons  = $tpl->get("ticketTypeIcons");

    $efforts        = $tpl->get('efforts');
    $priorities     = $tpl->get('priorities');
    $statusLabels   = $tpl->get('allTicketStates');


    $newField       = $tpl->get('newField');

    //All states >0 (<1 is archive)
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);

?>

<?php $tpl->displaySubmodule('tickets-ticketHeader') ?>

<div class="maincontent">

    <?php $tpl->displaySubmodule('tickets-ticketBoardTabs') ?>

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');

                $tpl->displaySubmodule('tickets-ticketNewBtn');
                $tpl->displaySubmodule('tickets-ticketFilter');

                $tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
                ?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">
                <div class="pull-right">

                    <?php $tpl->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                    <div id="tableButtons" style="display:inline-block"></div>

                    <?php $tpl->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>

                </div>
            </div>

        </div>

        <div class="clearfix" style="margin-bottom: 20px;"></div>



        <?php if (isset($allTicketGroups['all'])) {
            $allTickets = $allTicketGroups['all']['items'];
        }
        ?>

        <?php foreach ($allTicketGroups as $group) {?>
            <?php if ($group['label'] != 'all') { ?>
                <h5 class="accordionTitle <?=$group['class']?>" id="accordion_link_<?=$group['id'] ?>">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_<?=$group['id'] ?>" onclick="leantime.snippets.accordionToggle('<?=$group['id'] ?>');">
                        <i class="fa fa-angle-down"></i><?=$group['label'] ?>(<?=count($group['items']) ?>)
                    </a>
                </h5>
                <span><?=$group['more-info'] ?></span>
                <div class="simpleAccordionContainer" id="accordion_content-<?=$group['id'] ?>">
            <?php } ?>

                <?php $allTickets = $group['items']; ?>

                <?php $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTicketGroups]); ?>
                <table class="table table-bordered display ticketTable " style="width:100%">
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
                                <small><a href="#/tickets/showTicket/<?=$row['dependingTicketId'] ?>"><?=$tpl->escape($row['parentHeadline']) ?></a></small> //<br />
                            <?php } ?>
                            <a class='ticketModal' href="#/tickets/showTicket/<?=$tpl->e($row['id']); ?>"><?=$tpl->e($row['headline']); ?></a></td>



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
                            <td data-order="<?=$name ?>">
                                <div class="dropdown ticketDropdown statusDropdown colorized show ">
                                    <a class="dropdown-toggle status <?=$class ?>  f-left" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">


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

                            <td data-order="<?=$milestoneHeadline?>">
                                <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                    <a style="background-color:<?=$tpl->escape($row['milestoneColor'])?>" class="dropdown-toggle label-default milestone  f-left" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <td  data-order="<?=$row['storypoints'] ? $efforts['' . $row['storypoints'] . ''] ?? "?" : $tpl->__("label.story_points_unkown"); ?>">
                                <div class="dropdown ticketDropdown effortDropdown show">
                                    <a class="dropdown-toggle label-default effort  f-left" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                if ($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                                    echo $efforts["" . $row['storypoints']] ?? $row['storypoints'];
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

                            <td  data-order="<?php
                            if ($row['priority'] != '' && $row['priority'] > 0) {
                                echo $priorities[$row['priority']];
                            } else {
                                echo $tpl->__("label.priority_unkown");
                            }?>">
                                <div class="dropdown ticketDropdown priorityDropdown show">
                                    <a class="dropdown-toggle label-default priority priority-bg-<?=$row['priority']?>  f-left" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <td data-order="<?=$row["editorFirstname"] != "" ?  $tpl->escape($row["editorFirstname"]) : $tpl->__("dropdown.not_assigned")?>">
                                <div class="dropdown ticketDropdown userDropdown noBg show f-left">
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
                                $sprintHeadline = $tpl->__("links.no_list");
                            }?>

                            <td  data-order="<?=$sprintHeadline?>">

                                <div class="dropdown ticketDropdown sprintDropdown show">
                                    <a class="dropdown-toggle label-default sprint f-left" href="javascript:void(0);" role="button" id="sprintDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text"><?=$sprintHeadline?></span>
                                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="sprintDropdownMenuLink<?=$row['id']?>">
                                        <li class="nav-header border"><?=$tpl->__("dropdown.choose_list")?></li>
                                        <li class='dropdown-item'><a href='javascript:void(0);' data-label="<?=$tpl->__("label.not_assigned_to_list")?>" data-value='<?=$row['id'] . "_0"?>'> <?=$tpl->__("label.not_assigned_to_list")?> </a></li>
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
                                            echo"<span class='tag'><span>" . $tpl->escape($tag) . "</span></span>";
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
                                <input type="text" title="<?php echo $tpl->__("label.due"); ?>" value="<?php echo $date ?>" class="quickDueDates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
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
                                <?php echo app("blade.compiler")::render('@include("tickets::partials.ticketsubmenu", [
                                                                                        "ticket" => $ticket,
                                                                                        "onTheClock" => $onTheClock
                                                                                    ])', ['ticket' => $row, 'onTheClock' => $tpl->get("onTheClock")]); ?>


                            </td>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                        </tr>
                    <?php } ?>
                    <?php $tpl->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
                </tbody>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
                    <tfoot align="right">
                        <tr><td colspan="9"></td><td></td><td></td><td></td><td></td><td></td></tr>
                    </tfoot>

                </table>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>

            <?php if ($group['label'] != 'all') { ?>
                </div>
            <?php } ?>
        <?php } ?>




    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>


        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initUserDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initPriorityDropdown();
            leantime.ticketsController.initSprintDropdown();
            leantime.ticketsController.initStatusDropdown();

        <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");
        <?php } ?>



        leantime.ticketsController.initTicketsTable("<?=$searchCriteria["groupBy"] ?>");

        <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

    });

</script>
