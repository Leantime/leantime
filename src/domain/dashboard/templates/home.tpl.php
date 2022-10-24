<?php
	$states = $this->get('states');
    $projectProgress = $this->get('projectProgress');
    $projectProgress = $this->get('projectProgress');
    $sprintBurndown = $this->get('sprintBurndown');
    $backlogBurndown = $this->get('backlogBurndown');
    $efforts = $this->get('efforts');
    $statusLabels = $this->get('statusLabels');
    $currentUser = $this->get('currentUser');
    $allProjects = $this->get('allProjects');
    $projectFilter = $_SESSION['userHomeProjectFilter'] ?? '';
    $groupBy = $_SESSION['userHomeGroupBy'] ?? '';
    $milestones = $this->get('milestones');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-home"></span></div>
    <div class="pagetitle">
        <h1><?php echo $this->__("headlines.home"); ?></h1>

    </div>

</div>

<div class="maincontent">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="maincontentinner">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="todaysDate" style="padding-bottom:5px;"></h3>
                            <h1 class="articleHeadline"><?=$this->__('text.hi') ?> <?=$currentUser['firstname'] ?></h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">

                <div class="maincontentinner">
                    <div class="row" id="yourToDoContainer">
                    <div class="col-md-12">
                        <?php /*
                        if($login::userIsAtLeast($roles::$editor)) { ?>

                                <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link" onclick="jQuery('#ticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> <?php echo $this->__("links.quick_add_todo"); ?></a>
                                <div class="ticketBox hideOnLoad" id="ticket_new" style="padding:10px;">

                                    <form method="post" class="form-group">
                                        <input name="headline" type="text" title="<?php echo $this->__("label.headline"); ?>" style="width:100%" placeholder="<?php echo $this->__("input.placeholders.what_are_you_working_on"); ?>" />
                                        <input type="submit" value="<?php echo $this->__("buttons.save"); ?>" name="quickadd"  />
                                        <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                                        <input type="hidden" name="status" value="3" />
                                        <input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />
                                        <a href="javascript:void(0);" onclick="jQuery('#ticket_new').toggle('fast'); jQuery('#ticket_new_link').toggle('fast');">
                                            <?php echo $this->__("links.cancel"); ?>
                                        </a>
                                    </form>

                                    <div class="clearfix"></div>
                                    <br /><br />
                                </div>

                        <?php } */?>

                        <div class="marginBottomMd">

                            <form method="get" >
                                <div class="pull-left">
                                <h5 class="subtitle"><?=$this->__('headlines.your_todos'); ?></h5>
                                </div>

                                <div class="btn-group viewDropDown right">
                                    <button class="btn dropdown-toggle " type="button" data-toggle="dropdown"><?=$this->__("links.group_by") ?></button>
                                    <ul class="dropdown-menu">
                                        <li><span class="radio"><input type="radio" name="groupBy" <?php if($groupBy == "time"){echo "checked='checked'";}?> value="time" id="groupByDate" onclick="form.submit();"/><label for="groupByDate"><?=$this->__("label.dates") ?></label></span></li>
                                        <li><span class="radio"><input type="radio" name="groupBy" <?php if($groupBy == "project"){echo "checked='checked'";}?> value="project" id="groupByProject" onclick="form.submit();"/><label for="groupByProject"><?=$this->__("label.project") ?></label></span></li>
                                    </ul>
                                </div>
                                <div class="right">
                                    <label class="inline"><?=$this->__('label.show') ?></label>
                                    <select name="projectFilter" onchange="form.submit();">
                                        <option value=""><?=$this->__('labels.all_projects')?></option>
                                        <?php foreach($allProjects as $project) {?>
                                            <option value="<?=$project['id']?>"
                                                <?php if($projectFilter == $project['id']) echo "selected='selected'";?>
                                            ><?=$this->e($project['name'])?></option>
                                        <?php }?>
                                    </select>
                                    &nbsp;
                                </div>
                                <div class="clearall"></div>

                            </form>
                        </div>

                        <?php
                        if($this->get('tickets') !== null && count($this->get('tickets')) == 0){

                            echo"<div class='center'>";
                            echo"<div  style='width:30%' class='svgContainer'>";
                            echo file_get_contents(ROOT."/images/svg/undraw_a_moment_to_relax_bbpa.svg");
                            echo"</div>";
                            echo"<br /><h4>".$this->__("headlines.no_todos_this_week")."</h4>
                                        ".$this->__("text.take_the_day_off")."
                                        <a href='".BASE_URL."/tickets/showAll'>".$this->__("links.goto_backlog")."</a><br/><br/>
                            </div>";
                        }
                        ?>

                        <?php
                        $i=0;
                        foreach($this->get('tickets') as $ticketGroup) {
                            $i++;

                            //Get first duedate if exist
                            $ticketCreationDueDate = '';
                            if(isset($ticketGroup['tickets'][0])){

                                if($ticketGroup['tickets'][0]['dateToFinish'] != "0000-00-00 00:00:00" && $ticketGroup['tickets'][0]['dateToFinish'] != "1969-12-31 00:00:00") {
                                    //Use the first due date as the new due date
                                    $ticketCreationDueDate = $ticketGroup['tickets'][0]['dateToFinish'];
                                }
                            }

                            $groupProjectId = $_SESSION['currentProject'];
                            if($groupBy == 'project') {
                                if(isset($ticketGroup['tickets'][0])){
                                    $groupProjectId = $ticketGroup['tickets'][0]['projectId'];
                                }
                            }
                            ?>
                            <a class="anchor" id="accordion_anchor_<?=$i ?>"></a>
                            <h5 class="accordionTitle" id="accordion_link_<?=$i ?>">
                                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_<?=$i ?>" onclick="accordionToggle('<?=$i ?>');">
                                    <i class="fa fa-angle-down"></i><?=$this->__($ticketGroup["labelName"]) ?>
                                    (<?=count($ticketGroup["tickets"]) ?>)
                                </a>
                                <a class="titleInsertLink" href="javascript:void(0)" onclick="insertQuickAddForm(<?=$i; ?>, <?=$groupProjectId?>, '<?=$ticketCreationDueDate?>')"><i class="fa fa-plus"></i> <?=$this->__('links.add_todo_no_icon') ?></a>
                            </h5>
                            <div id="accordion_<?=$i ?>" class="simpleAccordionContainer">
                                <ul class="sortableTicketList" >

                                <?php if(count($ticketGroup['tickets']) == 0){?>
                                    <em>Nothing to see here. Move on.</em><br /><br />
                                <?php } ?>

                            <?php foreach($ticketGroup['tickets'] as $row) {

                                        if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                            $date = $this->__("text.anytime");

                                        }else {
                                            $date = new DateTime($row['dateToFinish']);
                                            $date = $date->format($this->__("language.dateformat"));

                                        }


                                        ?>
                                        <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                                            <div class="ticketBox fixed priority-border-<?=$row['priority']?>" data-val="<?php echo $row['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo $row['id'];?>">
                                                        <strong><a class='ticketModal' href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row['id'];?>" ><?php $this->e($row['headline']); ?></a></strong>

                                                        <?php if ($login::userIsAtLeast($roles::$editor)) {
                                                            $clockedIn = $this->get("onTheClock");
                                                            ?>

                                                            <div class="inlineDropDownContainer">
                                                                <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="nav-header"><?php echo $this->__("subtitles.todo"); ?></li>
                                                                    <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"]; ?>" class='ticketModal'><i class="fa fa-edit"></i> <?php echo $this->__("links.edit_todo"); ?></a></li>
                                                                    <li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete_todo"); ?></a></li>
                                                                    <li class="nav-header border"><?php echo $this->__("subtitles.track_time"); ?></li>
                                                                    <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                        <a class="punchIn" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="fa-regular fa-clock"></span> <?php echo $this->__("links.start_work"); ?></a>
                                                                        <a class="punchOut" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="fa-stop"></span> <?php if(is_array($clockedIn) == true) { echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), $clockedIn["since"])); }else{ echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), time())); }?></a>
                                                                        <span class='working' <?php if($clockedIn === false || $clockedIn["id"] === $row["id"]) { echo"style='display:none;'"; }?>><?php echo $this->__("text.timer_set_other_todo"); ?></span>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4" style="padding:0 15px;">
                                                        <?php echo $this->__("label.due"); ?><input type="text" title="<?php echo $this->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                                                    </div>
                                                    <div class="col-md-8" style="padding-top:3px;" >
                                                        <div class="right">

                                                            <div class="dropdown ticketDropdown effortDropdown show">
                                                                <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    if($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                                        echo $efforts[$row['storypoints']];
                                                                    }else{
                                                                        echo $this->__("label.story_points_unkown");
                                                                    }?>
                                                                </span>
                                                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink<?=$row['id']?>">
                                                                    <li class="nav-header border"><?=$this->__("dropdown.how_big_todo")?></li>
                                                                    <?php foreach($efforts as $effortKey => $effortValue){
                                                                        echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' data-value='".$row['id']."_".$effortKey."' id='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue."</a>";
                                                                        echo"</li>";
                                                                    }?>
                                                                </ul>
                                                            </div>


                                                            <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                                                <a style="background-color:<?=$this->escape($row['milestoneColor'])?>" class="dropdown-toggle f-left  label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    if($row['dependingTicketId'] != "" && $row['dependingTicketId'] != 0){
                                                                        $this->e($row['milestoneHeadline']);
                                                                    }else{
                                                                        echo $this->__("label.no_milestone");
                                                                    }?>
                                                                </span>
                                                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?=$row['id']?>">
                                                                    <li class="nav-header border"><?=$this->__("dropdown.choose_milestone")?></li>
                                                                    <li class='dropdown-item'><a style='background-color:#1b75bb' href='javascript:void(0);' data-label="<?=$this->__("label.no_milestone")?>" data-value='<?=$row['id']."_0_#1b75bb"?>'> <?=$this->__("label.no_milestone")?> </a></li>

                                                                    <?php

                                                                    foreach($milestones[$row['projectId']] as $milestone){
                                                                        echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' data-label='".$this->escape($milestone->headline)."' data-value='".$row['id']."_".$milestone->id."_".$this->escape($milestone->tags)."' id='ticketMilestoneChange".$row['id'].$milestone->id."' style='background-color:".$this->escape($milestone->tags)."'>".$this->escape($milestone->headline)."</a>";
                                                                        echo"</li>";
                                                                    }?>
                                                                </ul>
                                                            </div>

                                                            <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                                <a class="dropdown-toggle f-left status <?=$statusLabels[$row['projectId']][$row['status']]["class"]?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    echo $statusLabels[$row['projectId']][$row['status']]["name"];
                                                                    ?>
                                                                </span>
                                                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                                                    <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>

                                                                    <?php foreach($statusLabels[$row['projectId']] as $key=>$label){
                                                                        echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' class='".$label["class"]."' data-label='".$this->escape($label["name"])."' data-value='".$row['id']."_".$key."_".$label["class"]."' id='ticketStatusChange".$row['id'].$key."' >".$this->escape($label["name"])."</a>";
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
                <div class="maincontentinner">
                    <a href="<?=BASE_URL."/projects/showMy" ?>" class="pull-right"><?=$this->__('links.my_portfolio') ?></a>
                    <h5 class="subtitle"><?=$this->__("headline.your_projects") ?></h5>
                    <br/>
                    <?php if(count($allProjects) == 0) {

                        echo "<div class='col-md-12'><br /><br /><div class='center'>";
                        echo"<div style='width:70%' class='svgContainer'>";
                         echo $this->__('notifications.not_assigned_to_any_project');
                        if($login::userIsAtLeast($roles::$manager)){
                            echo"<br /><br /><a href='".BASE_URL."/projects/newProject' class='btn btn-primary'>".$this->__('link.new_project')."</a>";
                        }
                        echo"</div></div>";

                    }?>
                    <ul class="sortableTicketList" id="projectProgressContainer">
                        <?php foreach($allProjects as $project) {
                            $percentDone = round($project['progress']['percent']);
                            ?>
                            <li>
                                <div class="col-md-12">

                                <div class="row" >
                                    <div class="col-md-12 ticketBox fixed">

                                            <div class="row" style="padding-bottom:10px;">

                                                <div class="col-md-8">
                                                    <a href="<?=BASE_URL?>/dashboard/show?projectId=<?=$project['id']?>">
                                                        <?php $this->e($project['clientName'])?> \\
                                                        <?php $this->e($project['name'])?>
                                                    </a>
                                                </div>
                                                <div class="col-md-4" style="text-align:right">
                                                    <?=sprintf($this->__("text.percent_complete"), $percentDone)?>
                                                </div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentDone; ?>%">
                                                    <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $percentDone)?></span>
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

    function insertQuickAddForm(index, projectId, duedate) {
        jQuery(".quickaddForm").remove();

        jQuery("#accordion_"+index+" ul").prepend('<li class="quickaddForm">'+
            ' <div class="ticketBox" id="ticket_new_'+index+'" style="padding:18px;">'+
            '<form method="post" class="form-group" action="#accordion_anchor_'+index+'">'+
            '<input name="headline" type="text" title="<?php echo $this->__("label.headline"); ?>" style="width:100%" placeholder="<?php echo $this->__("input.placeholders.what_are_you_working_on"); ?>" />'+
            '<input type="submit" value="<?php echo $this->__("buttons.save"); ?>" name="quickadd"  />'+
            '<input type="hidden" name="dateToFinish" id="dateToFinish" value="'+duedate+'" />'+
            '<input type="hidden" name="status" value="3" />'+
            '<input type="hidden" name="projectId" value="'+projectId+'" />'+
            '<input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />&nbsp;'+
            '<a href="javascript:void(0);" onclick="jQuery(\'#ticket_new_'+index+'\').toggle(\'fast\');">'+
        '<?php echo $this->__("links.cancel"); ?>'+
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
       jQuery('.todaysDate').text(moment().format('LLLL'));

       <?php if($login::userIsAtLeast($roles::$editor)) { ?>
           leantime.dashboardController.prepareHiddenDueDate();
           leantime.ticketsController.initEffortDropdown();
           leantime.ticketsController.initMilestoneDropdown();
           leantime.ticketsController.initStatusDropdown();
           leantime.ticketsController.initDueDateTimePickers();
       <?php }else{ ?>
            leantime.generalController.makeInputReadonly(".maincontentinner");
       <?php } ?>



       <?php if(isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0){  ?>

            leantime.helperController.showHelperModal("dashboard", 500, 700);

            <?php
           //Only show once per session
            if(!isset($_SESSION['userdata']['settings']["modals"])) {
                $_SESSION['userdata']['settings']["modals"] = array();
            }

           if(!isset($_SESSION['userdata']['settings']["modals"]["dashboard"])) {
               $_SESSION['userdata']['settings']["modals"]["dashboard"] = 1;
           }

       } ?>



    });

</script>
