<?php
    foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
    $states = $tpl->get('states');
    $projectProgress = $tpl->get('projectProgress');
    $projectProgress = $tpl->get('projectProgress');
    $sprintBurndown = $tpl->get('sprintBurndown');
    $backlogBurndown = $tpl->get('backlogBurndown');
    $efforts = $tpl->get('efforts');
    $statusLabels = $tpl->get('statusLabels');
    $currentUser = $tpl->get('currentUser');
    $allProjects = $tpl->get('allProjects');
    $projectFilter = $_SESSION['userHomeProjectFilter'] ?? '';
    $groupBy = $_SESSION['userHomeGroupBy'] ?? '';
    $milestones = $tpl->get('milestones');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-home"></span></div>
    <div class="pagetitle">
        <h1><?php echo $tpl->__("headlines.home"); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div>
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">

        <?php echo $tpl->displayNotification(); ?>


        <div class="row">
            <div class="col-md-8">
                <div class="maincontentinner">

                    <?php
                        echo"<div class='pull-right' style='max-width:200px; padding:20px'>";
                        echo"<div  style='width:100%' class='svgContainer'>";
                        echo file_get_contents(ROOT . "/dist/images/svg/" . $tpl->get("randomImage"));
                        echo"</div></div>";

                    ?>

                    <h1 class="articleHeadline" style="padding-bottom:5px; padding-top:10px;">Welcome <strong><?php $tpl->e($currentUser['firstname']) ?></strong></h1>
                    <?php
                    $totalTickets = 0;
                    foreach ($tpl->get('tickets') as $ticketGroup) {
                        $totalTickets = $totalTickets + count($ticketGroup["tickets"]);
                    } ?>

                    <p>You have <strong><?=$totalTickets?> To-Dos</strong> across <strong><?=count($allProjects) ?> projects</strong> assigned to you.</p>
                    <?php $tpl->dispatchTplEvent('afterWelcomeMessage'); ?>
                    <div class="clear"></div>
                </div>

                <?php $tpl->dispatchTplEvent('afterWelcomeMessageBox'); ?>

                <div class="maincontentinner">
                    <div class="row" id="yourToDoContainer">
                    <div class="col-md-12">
                        <?php /*
                        if($login::userIsAtLeast($roles::$editor)) { ?>

                                <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link" onclick="jQuery('#ticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> <?php echo $tpl->__("links.quick_add_todo"); ?></a>
                                <div class="ticketBox hideOnLoad" id="ticket_new" style="padding:10px;">

                                    <form method="post" class="form-group">
                                        <input name="headline" type="text" title="<?php echo $tpl->__("label.headline"); ?>" style="width:100%" placeholder="<?php echo $tpl->__("input.placeholders.what_are_you_working_on"); ?>" />
                                        <input type="submit" value="<?php echo $tpl->__("buttons.save"); ?>" name="quickadd"  />
                                        <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                                        <input type="hidden" name="status" value="3" />
                                        <input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />
                                        <a href="javascript:void(0);" onclick="jQuery('#ticket_new').toggle('fast'); jQuery('#ticket_new_link').toggle('fast');">
                                            <?php echo $tpl->__("links.cancel"); ?>
                                        </a>
                                    </form>

                                    <div class="clearfix"></div>
                                    <br /><br />
                                </div>

                        <?php } */?>

                        <div class="marginBottomMd">

                            <form method="get" >
                                <div class="pull-left">
                                <h5 class="subtitle"><?=$tpl->__('headlines.your_todos'); ?></h5>
                                </div>

                                <div class="btn-group viewDropDown right">
                                    <button class="btn dropdown-toggle " type="button" data-toggle="dropdown"><?=$tpl->__("links.group_by") ?></button>
                                    <ul class="dropdown-menu">
                                        <li><span class="radio"><input type="radio" name="groupBy" <?php if ($groupBy == "time") {
                                            echo "checked='checked'";
                                                                                                   }?> value="time" id="groupByDate" onclick="form.submit();"/><label for="groupByDate"><?=$tpl->__("label.dates") ?></label></span></li>
                                        <li><span class="radio"><input type="radio" name="groupBy" <?php if ($groupBy == "project") {
                                            echo "checked='checked'";
                                                                                                   }?> value="project" id="groupByProject" onclick="form.submit();"/><label for="groupByProject"><?=$tpl->__("label.project") ?></label></span></li>
                                    </ul>
                                </div>
                                <div class="right">
                                    <label class="inline"><?=$tpl->__('label.show') ?></label>
                                    <select name="projectFilter" onchange="form.submit();">
                                        <option value=""><?=$tpl->__('labels.all_projects')?></option>
                                        <?php foreach ($allProjects as $project) {?>
                                            <option value="<?=$project['id']?>"
                                                <?php if ($projectFilter == $project['id']) {
                                                    echo "selected='selected'";
                                                }?>
                                            ><?=$tpl->e($project['name'])?></option>
                                        <?php }?>
                                    </select>
                                    &nbsp;
                                </div>
                                <div class="clearall"></div>

                            </form>
                        </div>

                        <?php
                        if ($tpl->get('tickets') !== null && count($tpl->get('tickets')) == 0) {
                            echo"<div class='center'>";
                            echo"<div  style='width:30%' class='svgContainer'>";
                            echo file_get_contents(ROOT . "/dist/images/svg/undraw_a_moment_to_relax_bbpa.svg");
                            echo"</div>";
                            echo"<br /><h4>" . $tpl->__("headlines.no_todos_this_week") . "</h4>
                                        " . $tpl->__("text.take_the_day_off") . "
                                        <a href='" . BASE_URL . "/tickets/showAll'>" . $tpl->__("links.goto_backlog") . "</a><br/><br/>
                            </div>";
                        }
                        ?>

                        <?php
                        $i = 0;
                        foreach ($tpl->get('tickets') as $ticketGroup) {
                            $i++;

                            //Get first duedate if exist
                            $ticketCreationDueDate = '';
                            if (isset($ticketGroup['tickets'][0])) {
                                if ($ticketGroup['tickets'][0]['dateToFinish'] != "0000-00-00 00:00:00" && $ticketGroup['tickets'][0]['dateToFinish'] != "1969-12-31 00:00:00") {
                                    //Use the first due date as the new due date
                                    $ticketCreationDueDate = $ticketGroup['tickets'][0]['dateToFinish'];
                                }
                            }

                            $groupProjectId = $_SESSION['currentProject'];
                            if ($groupBy == 'project') {
                                if (isset($ticketGroup['tickets'][0])) {
                                    $groupProjectId = $ticketGroup['tickets'][0]['projectId'];
                                }
                            }
                            ?>
                            <a class="anchor" id="accordion_anchor_<?=$i ?>"></a>

                            <h5 class="accordionTitle" id="accordion_link_<?=$i ?>">
                                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_<?=$i ?>" onclick="accordionToggle('<?=$i ?>');">
                                    <i class="fa fa-angle-down"></i><?=$tpl->__($ticketGroup["labelName"]) ?>
                                    (<?=count($ticketGroup["tickets"]) ?>)
                                </a>
                                <a class="titleInsertLink" href="javascript:void(0)" onclick="insertQuickAddForm(<?=$i; ?>, <?=$groupProjectId?>, '<?=$ticketCreationDueDate?>')"><i class="fa fa-plus"></i> <?=$tpl->__('links.add_todo_no_icon') ?></a>
                            </h5>
                            <div id="accordion_<?=$i ?>" class="yourToDoContainer simpleAccordionContainer">
                                <ul class="sortableTicketList" >

                                <?php if (count($ticketGroup['tickets']) == 0) {?>
                                    <em>Nothing to see here. Move on.</em><br /><br />
                                <?php } ?>

                            <?php foreach ($ticketGroup['tickets'] as $row) {
                                if ($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                    $date = $tpl->__("text.anytime");
                                } else {
                                    $date = new DateTime($row['dateToFinish']);
                                    $date = $date->format($tpl->__("language.dateformat"));
                                }


                                ?>
                                        <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                                            <div class="ticketBox fixed priority-border-<?=$row['priority']?>" data-val="<?php echo $row['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo $row['id'];?>">

                                                        <?php if ($login::userIsAtLeast($roles::$editor)) {
                                                            $clockedIn = $tpl->get("onTheClock");
                                                            ?>

                                                            <div class="inlineDropDownContainer">
                                                                <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="nav-header"><?php echo $tpl->__("subtitles.todo"); ?></li>
                                                                    <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"]; ?>" class='ticketModal'><i class="fa fa-edit"></i> <?php echo $tpl->__("links.edit_todo"); ?></a></li>
                                                                    <li><a href="<?=BASE_URL ?>/tickets/moveTicket/<?php echo $row["id"]; ?>" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $tpl->__("links.move_todo"); ?></a></li>
                                                                    <li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__("links.delete_todo"); ?></a></li>
                                                                    <li class="nav-header border"><?php echo $tpl->__("subtitles.track_time"); ?></li>
                                                                    <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                        <a class="punchIn" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if ($clockedIn !== false) {
                                                                            echo"style='display:none;'";
                                                                                                                                  }?>><span class="fa-regular fa-clock"></span> <?php echo $tpl->__("links.start_work"); ?></a>
                                                                        <a class="punchOut" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if ($clockedIn === false || $clockedIn["id"] != $row["id"]) {
                                                                            echo"style='display:none;'";
                                                                                                                                   }?>><span class="fa-stop"></span> <?php if (is_array($clockedIn) == true) {
                                                                                                                                   echo sprintf($tpl->__("links.stop_work_started_at"), date($tpl->__("language.timeformat"), $clockedIn["since"]));
                                                                                                                                   } else {
                                                                                                                                       echo sprintf($tpl->__("links.stop_work_started_at"), date($tpl->__("language.timeformat"), time()));
                                                                                                                                   }?></a>
                                                                        <span class='working' <?php if ($clockedIn === false || $clockedIn["id"] === $row["id"]) {
                                                                            echo"style='display:none;'";
                                                                                              }?>><?php echo $tpl->__("text.timer_set_other_todo"); ?></span>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                        <?php } ?>
                                                        <small><?=$tpl->e($row['projectName']) ?></small><br />
                                                        <?php if ($row['dependingTicketId'] > 0) { ?>
                                                            <a href="<?=BASE_URL?>/dashboard/home/#/tickets/showTicket/<?=$row['dependingTicketId'] ?>"><?=$tpl->escape($row['parentHeadline']) ?></a> //
                                                        <?php } ?>
                                                        <strong><a href="<?=BASE_URL ?>/dashboard/home/#/tickets/showTicket/<?php echo $row['id'];?>" ><?php $tpl->e($row['headline']); ?></a></strong>

                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4" style="padding:0 15px;">
                                                        <?php echo $tpl->__("label.due"); ?><input type="text" title="<?php echo $tpl->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                                                    </div>
                                                    <div class="col-md-8" style="padding-top:3px;" >
                                                        <div class="right">

                                                            <div class="dropdown ticketDropdown effortDropdown show">
                                                                <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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


                                                            <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                                                <a style="background-color:<?=$tpl->escape($row['milestoneColor'])?>" class="dropdown-toggle f-left  label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                if ($row['milestoneid'] != "" && $row['milestoneid'] != 0) {
                                                                    $tpl->e($row['milestoneHeadline']);
                                                                } else {
                                                                    echo $tpl->__("label.no_milestone");
                                                                }?>
                                                                </span>
                                                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?=$row['id']?>">
                                                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_milestone")?></li>
                                                                    <li class='dropdown-item'><a style='background-color:#b0b0b0' href='javascript:void(0);' data-label="<?=$tpl->__("label.no_milestone")?>" data-value='<?=$row['id'] . "_0_#b0b0b0"?>'> <?=$tpl->__("label.no_milestone")?> </a></li>

                                                                    <?php

                                                                    foreach ($milestones[$row['projectId']] as $milestone) {
                                                                        echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' data-label='" . $tpl->escape($milestone->headline) . "' data-value='" . $row['id'] . "_" . $milestone->id . "_" . $tpl->escape($milestone->tags) . "' id='ticketMilestoneChange" . $row['id'] . $milestone->id . "' style='background-color:" . $tpl->escape($milestone->tags) . "'>" . $tpl->escape($milestone->headline) . "</a>";
                                                                        echo"</li>";
                                                                    }?>
                                                                </ul>
                                                            </div>

                                                            <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                                <a class="dropdown-toggle f-left status <?=$statusLabels[$row['projectId']][$row['status']]["class"]?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                if (isset($statusLabels[$row['projectId']][$row['status']])) {
                                                                    echo $statusLabels[$row['projectId']][$row['status']]["name"];
                                                                } else {
                                                                    echo "unknown";
                                                                }
                                                                ?>
                                                                </span>
                                                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_status")?></li>

                                                                    <?php foreach ($statusLabels[$row['projectId']] as $key => $label) {
                                                                        echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' class='" . $label["class"] . "' data-label='" . $tpl->escape($label["name"]) . "' data-value='" . $row['id'] . "_" . $key . "_" . $label["class"] . "' id='ticketStatusChange" . $row['id'] . $key . "' >" . $tpl->escape($label["name"]) . "</a>";
                                                                        echo"</li>";
                                                                    }?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </li>
                                        <?php
                            } ?>
                                </ul>
                            </div>
                        <?php } ?>

                    </div>
                </div>
                </div>
            </div>

            <div class="col-md-4">

                <?php $tpl->dispatchTplEvent('beforeCalendar'); ?>

                <div class="maincontentinner minCalendar">

                    <button class="fc-next-button btn btn-default right" type="button" style="position:relative; z-index:9;">
                        <span class="fc-icon fc-icon-chevron-right"></span>
                    </button>
                    <button class="fc-prev-button btn btn-default right" type="button" style="margin-right:5px; position:relative; z-index:9;">
                        <span class="fc-icon fc-icon-chevron-left"></span>
                    </button>

                    <button class="fc-today-button btn btn-default right" style="margin-right:5px; position:relative; z-index:9;">today</button>

                    <div class="clear"></div>

                    <div id="calendar"></div>
                </div>

                <div class="maincontentinner">
                    <a href="<?=BASE_URL . "/projects/showMy" ?>" class="pull-right"><?=$tpl->__('links.my_portfolio') ?></a>
                    <h5 class="subtitle"><?=$tpl->__("headline.your_projects") ?></h5>
                    <br/>
                    <?php if (count($allProjects) == 0) {
                        echo "<div class='col-md-12'><br /><br /><div class='center'>";
                        echo"<div style='width:70%' class='svgContainer'>";
                         echo $tpl->__('notifications.not_assigned_to_any_project');
                        if ($login::userIsAtLeast($roles::$manager)) {
                            echo"<br /><br /><a href='" . BASE_URL . "/projects/newProject' class='btn btn-primary'>" . $tpl->__('link.new_project') . "</a>";
                        }
                        echo"</div></div>";
                    }?>
                    <ul class="sortableTicketList" id="projectProgressContainer">
                        <?php foreach ($allProjects as $project) {
                            $percentDone = round($project['progress']['percent']);
                            ?>
                            <li>
                                <div class="col-md-12">

                                <div class="row" >
                                    <div class="col-md-12 ticketBox fixed">

                                            <div class="row" style="padding-bottom:10px;">

                                                <div class="col-md-8">
                                                    <a href="<?=BASE_URL?>/dashboard/show?projectId=<?=$project['id']?>">
                                                        <span class="projectAvatar">
                                                            <img src="<?=BASE_URL?>/api/projects?projectAvatar=<?=$project['id']?>" />
                                                        </span>
                                                        <small><?php $tpl->e($project['clientName'])?></small><br />
                                                        <strong><?php $tpl->e($project['name'])?></strong>
                                                    </a>
                                                </div>
                                                <div class="col-md-4" style="text-align:right">
                                                    <?php if ($project['status'] !== null && $project['status'] != '') {?>
                                                        <span class="label label-<?php $tpl->e($project['status'])?>"><?=$tpl->__("label.project_status_" . $project['status']) ?></span><br />

                                                    <?php } else { ?>
                                                        <span class="label label-grey"><?=$tpl->__("label.no_status")?></span><br />
                                                    <?php } ?>

                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="col-md-7">
                                                    <?=$tpl->__("subtitles.project_progress") ?>
                                                </div>
                                                <div class="col-md-5" style="text-align:right">
                                                    <?=sprintf($tpl->__("text.percent_complete"), round($percentDone))?>
                                                </div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentDone; ?>%">
                                                    <span class="sr-only"><?=sprintf($tpl->__("text.percent_complete"), $percentDone)?></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">


                                                </div>
                                            </div>

                                    </div>
                                </div>

                            </div>
                            </li>
                        <?php }?>
                    </ul>
                </div>

            </div>
        </div>

</div>


<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

    function insertQuickAddForm(index, projectId, duedate) {
        jQuery(".quickaddForm").remove();

        jQuery("#accordion_"+index+" ul").prepend('<li class="quickaddForm">'+
            ' <div class="ticketBox" id="ticket_new_'+index+'" style="padding:18px;">'+
            '<form method="post" class="form-group" action="#accordion_anchor_'+index+'">'+
            '<input name="headline" type="text" title="<?php echo $tpl->__("label.headline"); ?>" style="width:100%" placeholder="<?php echo $tpl->__("input.placeholders.what_are_you_working_on"); ?>" />'+
            '<input type="submit" value="<?php echo $tpl->__("buttons.save"); ?>" name="quickadd"  />'+
            '<input type="hidden" name="dateToFinish" id="dateToFinish" value="'+duedate+'" />'+
            '<input type="hidden" name="status" value="3" />'+
            '<input type="hidden" name="projectId" value="'+projectId+'" />'+
            '<input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />&nbsp;'+
            '<a href="javascript:void(0);" onclick="jQuery(\'#ticket_new_'+index+'\').toggle(\'fast\');">'+
        '<?php echo $tpl->__("links.cancel"); ?>'+
        '</a>'+
        '</form></div></li>');

    }

    function accordionToggle(id) {

        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");



            if(currentLink.hasClass("fa-angle-right")){
                currentLink.removeClass("fa-angle-right");
                currentLink.addClass("fa-angle-down");
                jQuery('#accordion_'+id).slideDown("fast");
            }else{

                currentLink.removeClass("fa-angle-down");
                currentLink.addClass("fa-angle-right");

                jQuery('#accordion_'+id).slideUp("fast");
            }

    }

   jQuery(document).ready(function() {
       leantime.ticketsController.initModals();

       jQuery('.todaysDate').text(moment().format('LLLL'));

       <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
           leantime.dashboardController.prepareHiddenDueDate();
           leantime.ticketsController.initEffortDropdown();
           leantime.ticketsController.initMilestoneDropdown();
           leantime.ticketsController.initStatusDropdown();
           leantime.ticketsController.initDueDateTimePickers();
       <?php } else { ?>
            leantime.generalController.makeInputReadonly(".maincontentinner");
       <?php } ?>

       <?php if ($tpl->get('completedOnboarding') === false) { ?>
           leantime.helperController.firstLoginModal();
       <?php } ?>


       <?php
        if ($tpl->get('completedOnboarding') == "1" && (isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0)) {  ?>
            leantime.helperController.showHelperModal("dashboard", 500, 700);

             <?php
            //Only show once per session
                if (!isset($_SESSION['userdata']['settings']["modals"])) {
                      $_SESSION['userdata']['settings']["modals"] = array();
                }

                if (!isset($_SESSION['userdata']['settings']["modals"]["dashboard"])) {
                      $_SESSION['userdata']['settings']["modals"]["dashboard"] = 1;
                }
        } ?>




   });

    var events = [<?php foreach ($tpl->get('calendar') as $calendar) : ?>
        {

            title: <?php echo json_encode($calendar['title']); ?>,

            start: new Date(<?php echo
                $calendar['dateFrom']['y'] . ',' .
                ($calendar['dateFrom']['m'] - 1) . ',' .
                $calendar['dateFrom']['d'] . ',' .
                $calendar['dateFrom']['h'] . ',' .
                $calendar['dateFrom']['i'] ?>),
            <?php if (isset($calendar['dateTo'])) : ?>
            end: new Date(<?php echo
                $calendar['dateTo']['y'] . ',' .
                ($calendar['dateTo']['m'] - 1) . ',' .
                $calendar['dateTo']['d'] . ',' .
                $calendar['dateTo']['h'] . ',' .
                $calendar['dateTo']['i'] ?>),
            <?php endif; ?>
            <?php if ((isset($calendar['allDay']) && $calendar['allDay'] === true)) : ?>
            allDay: true,
            <?php else : ?>
            allDay: false,
            <?php endif; ?>
            enitityId: <?php echo $calendar['id'] ?>,
            <?php if (isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') : ?>
            url: '<?=CURRENT_URL ?>#/calendar/editEvent/<?php echo $calendar['id'] ?>',
            color: 'var(--accent2)',
            enitityType: "event",
            <?php else : ?>
            url: '<?=CURRENT_URL ?>#/tickets/showTicket/<?php echo $calendar['id'] ?>?projectId=<?php echo $calendar['projectId'] ?>',
            color: 'var(--accent1)',
            enitityType: "ticket",
            <?php endif; ?>
        },
                  <?php endforeach; ?>];



    document.addEventListener('DOMContentLoaded', function() {


        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
                height:'auto',
                initialView: 'multiMonthOneMonth',
                views: {
                    multiMonthOneMonth: {
                        type: 'multiMonth',
                        duration: { months: 1 },
                        multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                    }
                },
                events: events,
                editable: true,
                headerToolbar: false,

                nowIndicator: true,
                bootstrapFontAwesome: {
                    close: 'fa-times',
                    prev: 'fa-chevron-left',
                    next: 'fa-chevron-right',
                    prevYear: 'fa-angle-double-left',
                    nextYear: 'fa-angle-double-right'
                },
                eventDrop: function (event) {

                    if(event.event.extendedProps.enitityType == "ticket") {
                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/tickets',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                editFrom: event.event.startStr,
                                editTo: event.event.endStr
                            }
                        });

                    }else if(event.event.extendedProps.enitityType == "event") {

                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/calendar',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                dateFrom: event.event.startStr,
                                dateTo: event.event.endStr
                            }
                        })
                    }
                },
                eventResize: function (event) {

                    if(event.event.extendedProps.enitityType == "ticket") {
                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/tickets',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                editFrom: event.event.startStr,
                                editTo: event.event.endStr
                            }
                        })
                    }else if(event.event.extendedProps.enitityType == "event") {

                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/calendar',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                dateFrom: event.event.startStr,
                                dateTo: event.event.endStr
                            }
                        })
                    }

                },
                eventMouseEnter: function() {
                }
            }
        );
        calendar.setOption('locale', leantime.i18n.__("language.code"));
        calendar.render();
        calendar.scrollToTime( 100 );
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);

        jQuery('.fc-prev-button').click(function() {
            calendar.prev();
            calendar.getCurrentData()
            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery('.fc-next-button').click(function() {
            calendar.next();
            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery('.fc-today-button').click(function() {
            calendar.today();
            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery("#my-select").on("change", function(e){

            calendar.changeView(jQuery("#my-select option:selected").val());

            jQuery.ajax({
                type : 'PATCH',
                url  : leantime.appUrl + '/api/submenu',
                data : {
                    submenu : "myCalendarView",
                    state   : jQuery("#my-select option:selected").val()
                }
            });

        });
    });


    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
