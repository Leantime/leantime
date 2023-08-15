<?php

    defined('RESTRICTED') or die('Restricted access');

    echo $this->displayNotification();

    $tickets        = $this->get("tickets");
    $sprints        = $this->get("sprints");
    $searchCriteria = $this->get("searchCriteria");
    $currentSprint  = $this->get("currentSprint");

    $todoTypeIcons  = $this->get("ticketTypeIcons");

    $efforts        = $this->get('efforts');
    $priorities     = $this->get('priorities');



    //Count Columns to show
    $numberofColumns = count($this->get('allKanbanColumns'));

if ($numberofColumns > 0) {
    $size = floor(100 / $numberofColumns);
} else {
    $size = 100;
}

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

            </div>
        </div>

        <div class="clearfix"></div>

        <div id="sortableTicketKanban" class="sortableTicketList kanbanBoard">

            <div class="row-fluid">

                <?php

                foreach ($this->get('allKanbanColumns') as $key => $statusRow) {
                    ?>

                        <div class="column" style="width:<?=$size?>%;">

                            <h4 class="widgettitle title-primary title-border-<?php echo $statusRow['class']; ?>">
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <div class="inlineDropDownContainer" style="float:right;">
                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>

                                    <ul class="dropdown-menu">
                                        <li><a href="#/setting/editBoxLabel?module=ticketlabels&label=<?=$key?>" class="editLabelModal"><?=$this->__('headlines.edit_label')?></a>
                                        </li>
                                        <li><a href="<?=BASE_URL ?>/projects/showProject/<?=$_SESSION['currentProject'];?>#todosettings"><?=$this->__('links.add_remove_col')?></a></li>
                                    </ul>
                                </div>
                            <?php } ?>
                                <strong class="count">0</strong>
                            <?php $this->e($statusRow['name']); ?></h4>


                            <div class="contentInner <?php echo"status_" . $key;?>" >
                                <div>
                                    <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link_<?=$key?>" onclick="jQuery('#ticket_new_<?=$key?>').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> <?php echo $this->__("links.add_todo_no_icon"); ?></a>
                                    <div class="ticketBox hideOnLoad " id="ticket_new_<?=$key?>">

                                        <form method="post">
                                            <input type="text" name="headline" style="width:100%;" title="<?=$this->__("label.headline") ?>"/><br />
                                            <input type="hidden" name="milestone" value="<?php echo $searchCriteria['milestone']; ?>" />
                                            <input type="hidden" name="status" value="<?php echo $key; ?> " />
                                            <input type="hidden" name="sprint" value="<?php echo $_SESSION["currentSprint"]; ?> " />
                                            <input type="submit" value="Save" name="quickadd" />
                                            <a href="javascript:void(0);" onclick="jQuery('#ticket_new_<?=$key?>').toggle('fast'); jQuery('#ticket_new_link_<?=$key?>').toggle('fast');">
                                                <?=$this->__("links.cancel") ?>
                                            </a>
                                        </form>

                                        <div class="clearfix"></div>
                                    </div>
                                </div>

                                <?php foreach ($this->get('allTickets') as $row) { ?>
                                    <?php if ($row["status"] == $key) {?>
                                    <div class="ticketBox moveable container priority-border-<?=$row['priority']?>" id="ticket_<?php echo$row["id"];?>">

                                        <div class="row">

                                            <div class="col-md-12">


                                                <?php if ($login::userIsAtLeast($roles::$editor)) {
                                                    $clockedIn = $this->get("onTheClock");

                                                    ?>
                                                    <div class="inlineDropDownContainer" style="float:right;">

                                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class="nav-header"><?php echo $this->__("subtitles.todo"); ?></li>
                                                            <li><a href="#/tickets/showTicket/<?php echo $row["id"]; ?>" class=''><i class="fa fa-edit"></i> <?php echo $this->__("links.edit_todo"); ?></a></li>
                                                            <li><a href="#/tickets/moveTicket/<?php echo $row["id"]; ?>" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $this->__("links.move_todo"); ?></a></li>
                                                            <li><a href="#/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete_todo"); ?></a></li>
                                                            <li class="nav-header border"><?php echo $this->__("subtitles.track_time"); ?></li>
                                                            <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                <a class="punchIn" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if ($clockedIn !== false) {
                                                                    echo"style='display:none;'";
                                                                                                                          }?>><span class="fa-regular fa-clock"></span> <?php echo $this->__("links.start_work"); ?></a>
                                                                <a class="punchOut" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if ($clockedIn === false || $clockedIn["id"] != $row["id"]) {
                                                                    echo"style='display:none;'";
                                                                                                                           }?>><span class="fa fa-stop"></span> <?php if (is_array($clockedIn) == true) {
                                                                                                                           echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), $clockedIn["since"]));
                                                                                                                           } else {
                                                                                                                               echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), time()));
                                                                                                                           }?></a>
                                                                <span class='working' <?php if ($clockedIn === false || $clockedIn["id"] === $row["id"]) {
                                                                    echo"style='display:none;'";
                                                                                      }?>><?php echo $this->__("text.timer_set_other_todo"); ?></span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($row['dependingTicketId'] > 0) { ?>
                                                    <small><a href="#/tickets/showTicket/<?=$row['dependingTicketId'] ?>" class="form-modal"><?=$this->escape($row['parentHeadline']) ?></a></small> //
                                                <?php } ?>
                                                <small><i class="fa <?php echo $todoTypeIcons[strtolower($row['type'])]; ?>"></i> <?php echo $this->__("label." . strtolower($row['type'])); ?></small>
                                                <small>#<?php echo $row['id']; ?></small>
                                                <div class="kanbanCardContent">
                                                    <h4><a href="#/tickets/showTicket/<?php echo $row["id"];?>"><?php $this->e($row["headline"]);?></a></h4>

                                                    <div class="kanbanContent" style="margin-bottom: 20px">
                                                        <?php echo $this->escapeMinimal($row['description']); ?>
                                                    </div>

                                                </div>
                                                <?php if ($row['dateToFinish'] != "0000-00-00 00:00:00" && $row['dateToFinish'] != "1969-12-31 00:00:00") {
                                                    $date = new DateTime($row['dateToFinish']);
                                                    $date = $date->format($this->__("language.dateformat"));
                                                    echo $this->__("label.due_icon"); ?>
                                                    <input type="text" title="<?php echo $this->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" style="margin-left:0px;" data-id="<?php echo $row['id'];?>" name="date" />

                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="clearfix" style="padding-bottom: 8px;"></div>

                                        <div class="timerContainer " id="timerContainer-<?php echo $row["id"]; ?>" >

                                                <div class="dropdown ticketDropdown milestoneDropdown colorized show firstDropdown" >
                                                    <a style="background-color:<?=$this->escape($row['milestoneColor'])?>" class="dropdown-toggle f-left  label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text"><?php
                                                        if ($row['milestoneid'] != "" && $row['milestoneid'] != 0) {
                                                            $this->e($row['milestoneHeadline']);
                                                        } else {
                                                            echo $this->__("label.no_milestone");
                                                        }?>
                                                        </span>
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


                                            <?php if ($row['storypoints'] != '' && $row['storypoints'] > 0) { ?>
                                                <div class="dropdown ticketDropdown effortDropdown show">
                                                <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                                            <?php } ?>


                                                <div class="dropdown ticketDropdown priorityDropdown show">
                                                <a class="dropdown-toggle f-left  label-default priority priority-bg-<?=$row['priority']?>" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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


                                            <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="text">
                                                        <?php
                                                        if ($row["editorFirstname"] != "") {
                                                            echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=" . $row['editorId'] . "' width='25' style='vertical-align: middle;'/></span>";
                                                        } else {
                                                            echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=false' width='25' style='vertical-align: middle;'/></span>";
                                                        }?>
                                                    </span>
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                                    <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                                    <?php
                                                    if (is_array($this->get('users'))) {
                                                        foreach ($this->get('users') as $user) {
                                                            echo "<li class='dropdown-item'>
                                                                <a href='javascript:void(0);' data-label='" . sprintf(
                                                                $this->__("text.full_name"),
                                                                $this->escape($user["firstname"]),
                                                                $this->escape($user['lastname'])
                                                            ) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" . $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" . BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/>" . sprintf(
                                                                $this->__("text.full_name"),
                                                                $this->escape($user["firstname"]),
                                                                $this->escape($user['lastname'])
                                                            ) . "</a>";
                                                            echo "</li>";
                                                        }
                                                    }?>
                                                </ul>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>

                                        <?php if ($row["commentCount"] > 0 || $row["subtaskCount"] > 0 || $row['tags'] != '') {?>
                                        <div class="row">

                                            <div class="col-md-12 border-top" style="white-space: nowrap;">
                                                <?php if ($row["commentCount"] > 0) {?>
                                                    <a href="#/tickets/showTicket/<?php echo $row["id"];?>"><span class="fa-regular fa-comments"></span> <?php echo $row["commentCount"] ?></a>&nbsp;
                                                <?php } ?>

                                                <?php if ($row["subtaskCount"] > 0) {?>
                                                    <a id="subtaskLink_<?php echo $row["id"];?>" href="<?=CURRENT_URL ?>?tab=subtasks#/tickets/showTicket/<?php echo $row["id"];?>" class="subtaskLineLink"> <span class="fa fa-diagram-successor"></span> <?php echo $row["subtaskCount"] ?></a>&nbsp;
                                                <?php } ?>
                                                <?php if ($row['tags'] != '') {?>
                                                    <?php  $tagsArray = explode(",", $row['tags']); ?>
                                                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                                                        <i class="fa fa-tags" aria-hidden="true"></i> <?=count($tagsArray)?>
                                                    </a>
                                                    <ul class="dropdown-menu ">
                                                        <li style="padding:10px"><div class='tagsinput readonly'>
                                                        <?php

                                                        foreach ($tagsArray as $tag) {
                                                            echo"<span class='tag'><span>" . $tag . "</span></span>";
                                                        }

                                                        ?>
                                                            </div></li></ul>
                                                <?php } ?>

                                                <?php
                                                    /*<a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#files"><span class="fa-paper-clip"></span> <?php echo $row["fileCount"] ?></a>&nbsp;&nbsp;&nbsp;*/
                                                ?>



                                            </div>


                                        </div>
                                        <?php } ?>



                                    </div>
                                    <?php } ?>
                                <?php } ?>

                            </div>

                        </div>
                <?php } ?>

            </div>
            <div class="clearfix"></div>

        </div>
    </div>

</div>

<script type="text/javascript">

    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
        leantime.ticketsController.initUserDropdown();
        leantime.ticketsController.initMilestoneDropdown();
        leantime.ticketsController.initEffortDropdown();
        leantime.ticketsController.initPriorityDropdown();
        leantime.timesheetsController.initTicketTimers();

        var ticketStatusList = [<?php foreach ($this->get('allTicketStates') as $key => $statusRow) {
            echo "'" . $key . "',";
                                }?>];
        leantime.ticketsController.initTicketKanban(ticketStatusList);

    <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");
    <?php } ?>

    leantime.ticketsController.setUpKanbanColumns();

    jQuery(document).ready(function(){



        <?php if (isset($_GET['showTicketModal'])) {
            if ($_GET['showTicketModal'] == "") {
                $modalUrl = "";
            } else {
                $modalUrl = "/" . (int)$_GET['showTicketModal'];
            }
            ?>

        leantime.ticketsController.openTicketModalManually("<?=BASE_URL ?>/tickets/showTicket<?php echo $modalUrl; ?>");
        window.history.pushState({},document.title, '<?=BASE_URL ?>/tickets/showKanban');

        <?php } ?>


        <?php foreach ($this->get('allTickets') as $ticket) {
            if ($ticket['dependingTicketId'] > 0) {
                ?>
            var startElement = jQuery('#subtaskLink_<?=$ticket['dependingTicketId']; ?>')[0];
            var endElement =  document.getElementById('ticket_<?=$ticket['id']; ?>');

            if ( startElement != null && endElement != undefined) {


                var startAnchor = LeaderLine.mouseHoverAnchor({
                    element: startElement,
                    showEffectName: 'draw',
                    style: {background: 'none', backgroundColor: 'none'},
                    hoverStyle: {background: 'none', backgroundColor: 'none', cursor: 'pointer'}
                });

                var line<?=$ticket['id'] ?> = new LeaderLine(startAnchor, endElement, {
                    startPlugColor: 'var(--accent1)',
                    endPlugColor: 'var(--accent2)',
                    gradient: true,
                    size: 2,
                    path: "grid",
                    startSocket: 'bottom',
                    endSocket: 'auto'
                });

                jQuery("#ticket_<?=$ticket['id'] ?>").mousedown(function () {

                })
                    .mousemove(function () {

                    })
                    .mouseup(function () {
                        line<?=$ticket['id'] ?>.position();
                    });

                jQuery("#ticket_<?=$ticket['dependingTicketId'] ?>").mousedown(function () {

                    })
                    .mousemove(function () {


                    })
                    .mouseup(function () {
                        line<?=$ticket['id'] ?>.position();

                    });

            }

            <?php }
        } ?>




    });
</script>
