<?php

    defined('RESTRICTED') or die('Restricted access');

    echo $this->displayNotification();

    $searchCriteria = $this->get("searchCriteria");
    $currentSprint  = $this->get("currentSprint");
    $allTickets     = $this->get('allTickets');

    $todoTypeIcons  = $this->get("ticketTypeIcons");

    $efforts        = $this->get('efforts');
    $priorities     = $this->get('priorities');
    $statusLabels   = $this->get('allTicketStates');


    $newField       = $this->get('newField');

    //All states >0 (<1 is archive)
    $numberofColumns = count($this->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);

?>

<?php $this->displaySubmodule('tickets-ticketHeader') ?>

<div class="maincontent">

    <?php $this->displaySubmodule('tickets-ticketBoardTabs') ?>

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $this->dispatchTplEvent('filters.afterLefthandSectionOpen');

                $this->displaySubmodule('tickets-ticketNewBtn');
                $this->displaySubmodule('tickets-ticketFilter');

                $this->dispatchTplEvent('filters.beforeLefthandSectionClose');
                ?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">
                <div class="pull-right">

                    <?php $this->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                    <div id="tableButtons" style="display:inline-block"></div>

                    <?php $this->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>

        <?php $this->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

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
            <?php $this->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
            <thead>
                <?php $this->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                <tr>
                    <th class="id-col"><?= $this->__("label.id"); ?></th>
                    <th style="max-width: 350px;"><?= $this->__("label.title"); ?></th>
                    <th class="status-col"><?= $this->__("label.todo_status"); ?></th>
                    <th class="milestone-col"><?= $this->__("label.milestone"); ?></th>
                    <th class="effort-col"><?= $this->__("label.effort"); ?></th>
                    <th class="priority-col"><?= $this->__("label.priority"); ?></th>
                    <th class="user-col"><?= $this->__("label.editor"); ?>.</th>
                    <th class="sprint-col"><?= $this->__("label.sprint"); ?></th>
                    <th class="tags-col"><?= $this->__("label.tags"); ?></th>
                    <th class="duedate-col"><?= $this->__("label.due_date"); ?></th>
                    <th class="planned-hours-col"><?= $this->__("label.planned_hours"); ?></th>
                    <th class="remaining-hours-col"><?= $this->__("label.estimated_hours_remaining"); ?></th>
                    <th class="booked-hours-col"><?= $this->__("label.booked_hours"); ?></th>
                    <th class="no-sort"></th>
                </tr>
                <?php $this->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
            </thead>
            <?php $this->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
            <tbody>
                <?php $this->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                <?php foreach ($allTickets as $rowNum => $row) {?>
                    <tr style="height:1px;">
                        <?php $this->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                        <td data-order="<?=$this->e($row['id']); ?>">
                            #<?=$this->e($row['id']); ?>
                        </td>

                        <td data-order="<?=$this->e($row['headline']); ?>">
                            <?php if ($row['dependingTicketId'] > 0) { ?>
                                <small><a href="<?=$_SESSION['lastPage'] ?>/#/tickets/showTicket/<?=$row['dependingTicketId'] ?>"><?=$this->escape($row['parentHeadline']) ?></a></small> //<br />
                            <?php } ?>
                            <a class='ticketModal' href="<?=BASE_URL ?>/tickets/showTicket/<?=$this->e($row['id']); ?>"><?=$this->e($row['headline']); ?></a></td>



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
                                    <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>
                                    <?php foreach ($statusLabels as $key => $label) {
                                        echo"<li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='" . $label["class"] . "' data-label='" . $this->escape($label["name"]) . "' data-value='" . $row['id'] . "_" . $key . "_" . $label["class"] . "' id='ticketStatusChange" . $row['id'] . $key . "' >" . $this->escape($label["name"]) . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>



                        <?php
                        if ($row['milestoneid'] != "" && $row['milestoneid'] != 0) {
                            $milestoneHeadline = $this->escape($row['milestoneHeadline']);
                        } else {
                            $milestoneHeadline = $this->__("label.no_milestone");
                        }?>

                        <td class="dropdown-cell" data-order="<?=$milestoneHeadline?>">
                            <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                <a style="background-color:<?=$this->escape($row['milestoneColor'])?>" class="dropdown-toggle label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?=$milestoneHeadline?></span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.choose_milestone")?></li>
                                    <li class='dropdown-item'><a style='background-color:#b0b0b0' href='javascript:void(0);' data-label="<?=$this->__("label.no_milestone")?>" data-value='<?=$row['id'] . "_0_#b0b0b0"?>'> <?=$this->__("label.no_milestone")?> </a></li>

                                    <?php foreach ($this->get('milestones') as $milestone) {
                                        echo"<li class='dropdown-item'>
                                            <a href='javascript:void(0);' data-label='" . $this->escape($milestone->headline) . "' data-value='" . $row['id'] . "_" . $milestone->id . "_" . $this->escape($milestone->tags) . "' id='ticketMilestoneChange" . $row['id'] . $milestone->id . "' style='background-color:" . $this->escape($milestone->tags) . "'>" . $this->escape($milestone->headline) . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <td class="dropdown-cell"  data-order="<?=$row['storypoints'] ? $efforts[$row['storypoints']] : $this->__("label.story_points_unkown"); ?>">
                            <div class="dropdown ticketDropdown effortDropdown show">
                                <a class="dropdown-toggle label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?php
                                                            if ($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                                echo $efforts[$row['storypoints']];
                                                            } else {
                                                                echo $this->__("label.story_points_unkown");
                                                            }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.how_big_todo")?></li>
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
                            echo $this->__("label.priority_unkown");
                        }?>">
                            <div class="dropdown ticketDropdown priorityDropdown show">
                                <a class="dropdown-toggle label-default priority priority-bg-<?=$row['priority']?>" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?php
                                                            if ($row['priority'] != '' && $row['priority'] > 0) {
                                                                echo $priorities[$row['priority']];
                                                            } else {
                                                                echo $this->__("label.priority_unkown");
                                                            }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="priorityDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.select_priority")?></li>
                                    <?php foreach ($priorities as $priorityKey => $priorityValue) {
                                        echo"<li class='dropdown-item'>
                                             <a href='javascript:void(0);' class='priority-bg-" . $priorityKey . "' data-value='" . $row['id'] . "_" . $priorityKey . "' id='ticketPriorityChange" . $row['id'] . $priorityKey . "'>" . $priorityValue . "</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <td class="dropdown-cell"  data-order="<?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?>">
                            <div class="dropdown ticketDropdown userDropdown noBg show ">
                                <a class="dropdown-toggle" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                <?php if ($row["editorFirstname"] != "") {
                                                                    echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=" . $row['editorId'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $this->escape($row["editorFirstname"]) . "</span>";
                                                                } else {
                                                                    echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $this->__("dropdown.not_assigned") . "</span>";
                                                                }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                    <?php foreach ($this->get('users') as $user) {
                                        echo "<li class='dropdown-item'>";
                                        echo "<a href='javascript:void(0);' data-label='" . sprintf($this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname'])) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" . $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" . BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/>" . sprintf($this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname'])) . "</a>";
                                        echo "</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <?php

                        if ($row['sprint'] != "" && $row['sprint'] != 0  && $row['sprint'] != -1) {
                            $sprintHeadline = $this->escape($row['sprintName']);
                        } else {
                            $sprintHeadline = $this->__("label.backlog");
                        }?>

                        <td class="dropdown-cell"  data-order="<?=$sprintHeadline?>">

                            <div class="dropdown ticketDropdown sprintDropdown show">
                                <a class="dropdown-toggle label-default sprint" href="javascript:void(0);" role="button" id="sprintDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="text"><?=$sprintHeadline?></span>
                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="sprintDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.choose_sprint")?></li>
                                    <li class='dropdown-item'><a href='javascript:void(0);' data-label="<?=$this->__("label.backlog")?>" data-value='<?=$row['id'] . "_0"?>'> <?=$this->__("label.backlog")?> </a></li>
                                    <?php if ($this->get('sprints')) {
                                        foreach ($this->get('sprints') as $sprint) {
                                            echo "<li class='dropdown-item'>
                                                    <a href='javascript:void(0);' data-label='" . $this->escape($sprint->name) . "' data-value='" . $row['id'] . "_" . $sprint->id . "' id='ticketSprintChange" . $row['id'] . $sprint->id . "' >" . $this->escape($sprint->name) . "</a>";
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
                            $date = $this->__("text.anytime");
                        } else {
                            $date = new DateTime($row['dateToFinish']);
                            $date = $date->format($this->__("language.dateformat"));
                        }
                        ?>
                        <td data-order="<?=$row['dateToFinish'] ?>" >
                            <input type="text" title="<?php echo $this->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                        </td>
                        <td data-order="<?=$this->e($row['planHours']); ?>">
                            <input type="text" value="<?=$this->e($row['planHours']); ?>" name="planHours" class="small-input secretInput" onchange="leantime.ticketsController.updatePlannedHours(this, '<?=$row['id']?>'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                        </td>
                        <td data-order="<?=$this->e($row['hourRemaining']); ?>">
                            <input type="text" value="<?=$this->e($row['hourRemaining']); ?>" name="remainingHours" class="small-input secretInput" onchange="leantime.ticketsController.updateRemainingHours(this, '<?=$row['id']?>');" />
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
                                $clockedIn = $this->get("onTheClock");

                                ?>
                                <div class="inlineDropDownContainer">

                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="nav-header"><?php echo $this->__("subtitles.todo"); ?></li>
                                        <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"]; ?>" class='ticketModal'><i class="fa fa-edit"></i> <?php echo $this->__("links.edit_todo"); ?></a></li>
                                        <li><a href="<?=BASE_URL ?>/tickets/moveTicket/<?php echo $row["id"]; ?>" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $this->__("links.move_todo"); ?></a></li>
                                        <li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete_todo"); ?></a></li>
                                        <li class="nav-header border"><?php echo $this->__("subtitles.track_time"); ?></li>
                                        <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                            <a
                                                class="punchIn"
                                                href="javascript:void(0);"
                                                data-value="<?php echo $row["id"]; ?>"
                                                <?php if ($clockedIn !== false) {
                                                    echo"style='display:none;'";
                                                } ?>
                                            ><span class="fa-regular fa-clock"></span> <?php echo $this->__("links.start_work"); ?></a>
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
                                                    echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), $clockedIn["since"]));
                                                } else {
                                                    echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), time()));
                                                } ?>
                                            </a>
                                            <span
                                                class='working'
                                                <?php if ($clockedIn === false || $clockedIn["id"] === $row["id"]) {
                                                    echo"style='display:none;'";
                                                } ?>
                                            >
                                                <?php echo $this->__("text.timer_set_other_todo"); ?>
                                            </span>
                                        </li>
                                    </ul>
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
        <?php $this->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>


        leantime.ticketsController.initModals();

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

        <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

    });

</script>
