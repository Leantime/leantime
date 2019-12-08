<?php
	$states = $this->get('states');
    $projectProgress = $this->get('projectProgress');
    $projectProgress = $this->get('projectProgress');
    $sprintBurndown = $this->get('sprintBurndown');
    $backlogBurndown = $this->get('backlogBurndown');
    $efforts = $this->get('efforts');
    $statusLabels = $this->get('statusLabels');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-home"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h5><?php $this->e($_SESSION["currentProjectClient"]); ?></h5>
                <h1><?php echo $this->__("headlines.project_with_name"); ?> <?php $this->e($this->get('currentProjectName')); ?></h1>
            </div>
            <div class="col-lg-4" style="text-align:right;padding-top:15px">
                <?php if(count($this->get('allUsers')) == 1) {?>

                        <a href="/users/newUser/" >
                            <i class="fa fa-users" style="font-size:25px; margin-right:10px; vertical-align: middle"></i>
                            <span style="font-size:14px; line-height:25px;">
                                <?php echo $this->__("links.dont_do_it_alone"); ?>
                            </span>
                        </a>

                <?php } ?>

            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="row" id="sprintBurndownChart">
                    <div class="col-md-12">

                        <?php if($sprintBurndown != []){ ?>

                            <h5 class="subtitle"><?php echo $this->__("subtitles.sprint_burndown"); ?></h5>

                            <div class="pull-right">
                                <div class="btn-group mt-1 mx-auto" role="group">
                                    <a href="javascript:void(0)" id="NumChartButton" class="btn btn-sm btn-secondary active"><?php echo $this->__("links.num_of_todos"); ?></a>
                                    <a href="javascript:void(0)" id="EffortChartButton" class="btn btn-sm btn-secondary "><?php echo $this->__("links.effort"); ?></a>
                                    <a href="javascript:void(0)" id="HourlyChartButton" class="btn btn-sm btn-secondary"><?php echo $this->__("links.hourly"); ?></a>
                                </div>

                            </div>
                            <?php
                            $dates = date($this->__("language.dateformat"), strtotime($this->get('currentSprint')->startDate)) ." - " .date($this->__("language.dateformat"), strtotime($this->get('currentSprint')->endDate));
                            echo "<h4 class='inline'>".$this->get('currentSprint')->name."</h4> - ".$dates; ?>
                            <div style="width:100%; height:350px;">
                                <canvas id="sprintBurndown"></canvas>
                            </div>
                            <div style="clear:both;"></div>
                            <br /><br /><br />


                        <?php }else if($backlogBurndown != []) { ?>

                            <h5 class="subtitle"><?php echo $this->__("subtitles.backlog_burndown"); ?></h5>

                            <div class="pull-right">
                                <div class="btn-group mt-1 mx-auto" role="group">
                                    <a href="javascript:void(0)" id="NumChartButton"
                                       class="btn btn-sm btn-secondary active"><?php echo $this->__("links.num_of_todos"); ?></a>
                                    <a href="javascript:void(0)" id="EffortChartButton"
                                       class="btn btn-sm btn-secondary "><?php echo $this->__("links.effort"); ?></a>
                                    <a href="javascript:void(0)" id="HourlyChartButton"
                                       class="btn btn-sm btn-secondary"><?php echo $this->__("links.hourly"); ?></a>


                                </div>

                            </div>
                            <div style="width:100%; height:350px;">
                                <canvas id="backlogBurndown"></canvas>
                            </div>
                            <div style="clear:both;"></div>
                            <br /><br /><br />
                            <?php
                        }else {
                            if ($this->get('upcomingSprint') == false) {
                                echo "<div class='emptyChartContainer'>
                                <h4>".$this->__("headlines.no_active_sprint")."</h4>
                                ".$this->__("headlines.no_active_sprint")."<br /><br />
                                <a href='/sprints/editSprint' class=\"sprintModal btn btn-primary\" ><span class=\"fa fa-rocket\"></span> ".$this->__("links.create_sprint")."</a>
                                </div>";
                            } else {
                                echo "<div class='emptyChartContainer'>
                                <h4>".sprintf($this->__("headlines.sprint_starts_on"), date($this->__("language.dateformat"), strtotime($this->get('upcomingSprint')->startDate)))."</h4>
                                ".$this->__("text.update_sprint_chart_add_todos")."<br /><br />
                                <a href='/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> ".$this->__("links.goto_backlog")."</a>
                                </div>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="row" id="yourToDoContainer">
                    <div class="col-md-12">
                        <h5 class="subtitle">
                            <?php echo sprintf($this->__("subtitles.todos_this_week"), count($this->get('tickets')["thisWeek"])); ?>
                        </h5>


                        <ul class="sortableTicketList" >
                            <li class="">
                                <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link"  onclick="jQuery('#ticket_new').toggle('fast'); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> <?php echo $this->__("links.quick_add_todo"); ?></a>
                                <div class="ticketBox hideOnLoad" id="ticket_new" style="text-align:center;">

                                    <form method="post" class="form-group">
                                        <input name="headline" type="text" title="<?php echo $this->__("label.headline"); ?>" style="width:30%;" />
                                        <input type="submit" value="<?php echo $this->__("buttons.save"); ?>" name="quickadd" style="margin-top:-1px;" />
                                        <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                                        <input type="hidden" name="status" value="3" />
                                        <input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />
                                        <a href="javascript:void(0);" class="delete" onclick="jQuery('#ticket_new').toggle('fast'); jQuery('#ticket_new_link').toggle('fast');">
                                            <i class="fas fa-times"></i> <?php echo $this->__("links.cancel"); ?>
                                        </a>
                                    </form>

                                    <div class="clearfix"></div>
                                </div>
                            </li>
                            <?php
                                if(count($this->get('tickets')["thisWeek"]) == 0){

                                    echo"<div class='center'><br /><h4>".$this->__("headlines.no_todos_this_week")."</h4>
                                        ".$this->__("text.take_the_day_off")."<br/><br/>
                                        <h4><a href='/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> ".$this->__("links.goto_backlog")."</a>";
                                }

                                foreach($this->get('tickets')["thisWeek"] as $row){

                                    if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                        $date = $this->__("text.anytime");

                                    }else {
                                        $date = new DateTime($row['dateToFinish']);
                                        $date = $date->format($this->__("language.dateformat"));

                                    }


                                    ?>
                                    <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                                        <div class="ticketBox fixed" data-val="<?php echo $row['id']; ?>">
                                            <div class="row">
                                                <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo $row['id'];?>">
                                                    <strong><a href="/tickets/showTicket/<?php echo $row['id'];?>" ><?php $this->e($row['headline']); ?></a></strong>

                                                    <?php

                                                    if ($_SESSION['userdata']['role'] !== 'user') {
                                                        $clockedIn = $this->get("onTheClock");
                                                    ?>

                                                        <div class="inlineDropDownContainer">
                                                            <a href="/users/editOwn/" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li class="nav-header"><?php echo $this->__("subtitles.todo"); ?></li>
                                                                <li><a href="/tickets/showTicket/<?php echo $row["id"]; ?>"><i class="fa fa-edit"></i> <?php echo $this->__("links.edit_todo"); ?></a></li>
                                                                <li><a href="/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete_todo"); ?></a></li>
                                                                <li class="nav-header border"><?php echo $this->__("subtitles.track_time"); ?></li>
                                                                <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                    <a class="punchIn" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="iconfa-time"></span> <?php echo $this->__("links.start_work"); ?></a>
                                                                    <a class="punchOut" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="iconfa-stop"></span> <?php echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), $clockedIn["since"])); ?></a>
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

                                                                <?php foreach($this->get('milestones') as $milestone){
                                                                    echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' data-label='".$this->escape($milestone->headline)."' data-value='".$row['id']."_".$milestone->id."_".$this->escape($milestone->tags)."' id='ticketMilestoneChange".$row['id'].$milestone->id."' style='background-color:".$this->escape($milestone->tags)."'>".$this->escape($milestone->headline)."</a>";
                                                                    echo"</li>";
                                                                }?>
                                                            </ul>
                                                        </div>

                                                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                            <a class="dropdown-toggle f-left status <?=$statusLabels[$row['status']]["class"]?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    echo $statusLabels[$row['status']]["name"];
                                                                    ?>
                                                                </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                                                <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>

                                                                <?php foreach($statusLabels as $key=>$label){
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

                        <br /><br />
                        <h5 class="subtitle"><?php echo sprintf($this->__("subtitles.todos_later"), count($this->get('tickets')["later"])) ?></h5>

                        <ul class="sortableTicketList" >

                            <?php
                            if(count($this->get('tickets')["later"]) == 0){
                                echo"<div class='center'><br /><h4>".$this->__("headlines.no_todos")."</h4>".$this->__("text.start_filling_backlog")."<br/><br/><h4><a href='/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> ".$this->__("links.goto_backlog")."</a>";
                            }
                            ?>
                            <?php foreach($this->get('tickets')["later"] as $row){

                                if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                    $date = $this->__("text.anytime");

                                }else {
                                    $date = new DateTime($row['dateToFinish']);
                                    $date = $date->format($this->__("language.dateformat"));
                                }
                                ?>
                                <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                                    <div class="ticketBox fixed" data-val="<?php echo $row['id']; ?>">
                                        <div class="row">
                                            <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo $row['id'];?>">
                                                <strong><a href="/tickets/showTicket/<?php echo $row['id'];?>" ><?php $this->e($row['headline']); ?></a></strong>

                                                <?php

                                                if ($_SESSION['userdata']['role'] !== 'user') {
                                                    $clockedIn = $this->get("onTheClock");
                                                    ?>

                                                    <div class="inlineDropDownContainer">
                                                        <a href="/users/editOwn/" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class="nav-header"><?php echo $this->__("subtitles.todo"); ?></li>
                                                            <li><a href="/tickets/showTicket/<?php echo $row["id"]; ?>"><i class="fa fa-edit"></i> <?php echo $this->__("links.edit_todo"); ?></a></li>
                                                            <li><a href="/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete_todo"); ?></a></li>
                                                            <li class="nav-header border"><?php echo $this->__("subtitles.track_time"); ?></li>
                                                            <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                <a class="punchIn" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="iconfa-time"></span> <?php echo $this->__("links.start_work"); ?></a>
                                                                <a class="punchOut" href="javascript:void(0);" data-value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="iconfa-stop"></span> <?php echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), $clockedIn["since"])); ?></a>
                                                                <span class='working' <?php if($clockedIn === false || $clockedIn["id"] === $row["id"]) { echo"style='display:none;'"; }?>><?php echo $this->__("text.timer_set_other_todo"); ?></span>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4" style="padding:0 15px;">
                                                <?php echo $this->__("label.due"); ?><input type="text" title="<?php echo $this->__("label.due");?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
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

                                                            <?php foreach($this->get('milestones') as $milestone){
                                                                echo"<li class='dropdown-item'>
                                                                    <a href='javascript:void(0);' data-label='".$this->escape($milestone->headline)."' data-value='".$row['id']."_".$milestone->id."_".$this->escape($milestone->tags)."' id='ticketMilestoneChange".$row['id'].$milestone->id."' style='background-color:".$this->escape($milestone->tags)."'>".$this->escape($milestone->headline)."</a>";
                                                                echo"</li>";
                                                            }?>
                                                        </ul>
                                                    </div>

                                                    <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                        <a class="dropdown-toggle f-left status <?=$statusLabels[$row['status']]["class"]?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    echo $statusLabels[$row['status']]["name"];
                                                                    ?>
                                                                </span>
                                                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                                            <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>

                                                            <?php foreach($statusLabels as $key=>$label){
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
                </div>

            </div>

            <div class="col-lg-4">

                <div class="row" id="projectProgressContainer">
                    <div class="col-md-12">


                        <h5 class="subtitle"><?=$this->__("subtitles.project_progress")?></h5>

                        <div id="canvas-holder" style="width:100%; height:250px;">
                            <canvas id="chart-area" ></canvas>
                        </div>
                        <div style="text-align:center">
                            <?=sprintf($this->__("text.estimated_date_of_completion"), $projectProgress['estimatedCompletionDate']); ?>
                        </div>
                        <br /><br />
                    </div>
                </div>
                <div class="row" id="milestoneProgressContainer">
                    <div class="col-md-12">
                        <h5 class="subtitle"><?=$this->__("headlines.no_milestones") ?></h5>
                        <ul class="sortableTicketList" >
                            <?php
                            if(count($this->get('milestones')) == 0){
                                echo"<div class='center'><br /><h4>".$this->__("headlines.no_milestones")."</h4>
                                ".$this->__("text.milestones_help_organize_projects")."<br /><br /><a href='/tickets/roadmap' class='btn btn-primary'><span class=\"fas fa-map\"></span> ".$this->__("links.goto_milestones")."</a>";
                            }
                            ?>
                            <?php foreach($this->get('milestones') as $row){
                                $percent = 0;

                                if($row->allTickets != 0 ) {
                                    $percent = round(($row->doneTickets/$row->allTickets)*100);
                                }
                                if($row->editTo == "0000-00-00 00:00:00") {
                                    $date = $this->__("text.no_date_defined");
                                }else {
                                    $date = new DateTime($row->editTo);
                                    $date= $date->format($this->__("language.dateformat"));
                                }
                                if($percent < 100 || $date >= new DateTime()) {
                                    ?>
                                    <li class="ui-state-default" id="milestone_<?php echo $row->id; ?>" >
                                        <div class="ticketBox fixed">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong><a href="/tickets/showKanban&milestone=<?php echo $row->id;?>" ><?php $this->e($row->headline); ?></a></strong>
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="col-md-7">
                                                    <?=$this->__("label.due") ?>
                                                    <?php echo $date; ?>
                                                </div>
                                                <div class="col-md-5" style="text-align:right">
                                                    <?=sprintf($this->__("text.percent_complete"), $percent)?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%">
                                                            <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $percent)?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php }
                            } ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

   jQuery(document).ready(function() {

       leantime.dashboardController.prepareHiddenDueDate();

       leantime.dashboardController.initProgressChart(<?php echo round($projectProgress['percent']); ?>, <?php echo round((100 - $projectProgress['percent'])); ?>);

       <?php if($sprintBurndown != []){ ?>

           leantime.dashboardController.initBurndown([<?php foreach($sprintBurndown as $value) echo "'".$value['date']."',"; ?>], [<?php foreach($sprintBurndown as $value) echo "'".round($value['plannedNum'], 2)."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedHours']."',"; ?>], [ <?php foreach($sprintBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ]);
           leantime.dashboardController.initChartButtonClick('EffortChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedEffort']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ]);
           leantime.dashboardController.initChartButtonClick('NumChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedNum']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);

       <?php } ?>

       <?php if($backlogBurndown != []){ ?>

           leantime.dashboardController.initBacklogBurndown([<?php foreach($backlogBurndown as $value) echo "'".$value['date']."',"; ?>], [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);

           leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButton', [ <?php foreach($backlogBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ]);
           leantime.dashboardController.initBacklogChartButtonClick('EffortChartButton', [ <?php foreach($backlogBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ]);
           leantime.dashboardController.initBacklogChartButtonClick('NumChartButton', [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);

       <?php } ?>

       <?php if(isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0){  ?>

           leantime.helperController.showHelperModal("dashboard", 500, 700);

       <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["dashboard"] = 1;
       } ?>

    });

</script>