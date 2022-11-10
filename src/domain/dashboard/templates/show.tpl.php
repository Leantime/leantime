<?php
	$states = $this->get('states');
    $projectProgress = $this->get('projectProgress');
    $projectProgress = $this->get('projectProgress');
    $sprintBurndown = $this->get('sprintBurndown');
    $backlogBurndown = $this->get('backlogBurndown');
    $efforts = $this->get('efforts');
    $statusLabels = $this->get('statusLabels');
    $project = $this->get('project');
    $tickets = $this->get('tickets');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-home"></span></div>
    <div class="pagetitle">

        <?php if(count($this->get('allUsers')) == 1) {?>
            <a href="<?=BASE_URL ?>/users/newUser/" class="headerCTA">
                <i class="fa fa-users"></i>
                <span style="font-size:14px; line-height:25px;">
                        <?php echo $this->__("links.dont_do_it_alone"); ?>
                    </span>
            </a>
        <?php } ?>

        <h5><?php $this->e($_SESSION["currentProjectClient"]); ?></h5>
        <h1><?php echo $this->__("headlines.project_dashboard"); ?></h1>

    </div>

</div>

<div class="maincontent">


        <?php echo $this->displayNotification(); ?>

        <div class="row">

            <div class="col-md-8">
                <div class="maincontentinner">
                    <h3><?php $this->e($_SESSION["currentProjectClient"]); ?></h3>
                    <h1 class="articleHeadline"><?php $this->e($this->get('currentProjectName')); ?></h1>
                    <?=$this->escapeMinimal($project['details']) ?>
                    <br />
                </div>
                <div class="maincontentinner">
                    <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                        <div class="pull-right">
                            <a class="titleInsertLink" href="<?=BASE_URL?>/projects/showProject/<?=$project['id']?>#team"><i class="fa fa-user-plus"></i> <?=$this->__('links.add_team_member') ?></a>
                        </div>
                    <?php } ?>
                    <h5 class="subtitle"><?=$this->__('tabs.team') ?></h5>
                    <div class="row teamBox">
                        <?php foreach($project['assignedUsers'] as $userId => $assignedUser){?>

                            <div class="col-md-3">
                                <div class="profileBox">
                                    <div class="commentImage">
                                        <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $assignedUser['profileId'] ?>"/>
                                    </div>
                                    <span class="userName"><?php printf( $this->__('text.full_name'), $this->escape($assignedUser['firstname']), $this->escape($assignedUser['lastname'])); ?></span>
                                    <?=$this->__("label.roles.".$roles::getRoles()[$assignedUser['role']]) ?>
                                    <div class="clearall"></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="maincontentinner">
                    <h5 class="subtitle"><?=$this->__('headlines.latest_todos')?></h5>
                    <br />
                    <ul class="sortableTicketList" >

                        <?php if(count($tickets) == 0){?>
                            <em>Nothing to see here. Move on.</em><br /><br />
                        <?php } ?>

                        <?php foreach($tickets as $row) {

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

            <div class="col-md-4">
                <div class="maincontentinner">
                    <div class="pull-right">
                        <?php if($login::userIsAtLeast($roles::$editor)){ ?>
                        <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)" id="mainToggler">
                            <span class="fa fa-plus-square"></span> <?php echo $this->__('links.add_new_report') ?>
                        </a>
                        <?php } ?>
                    </div>
                    <h5 class="subtitle">
                        <?=$this->__('subtitles.project_updates') ?>
                    </h5>

                    <form method="post" action="<?=BASE_URL ?>/dashboard/show">
                        <input type="hidden" name="comment" value="1" />
                        <?php

                        $comments = new leantime\domain\repositories\comments();
                        $formUrl = CURRENT_URL;

                        //Controller may not redirect. Make sure delComment is only added once
                        if (strpos($formUrl, '?delComment=') !== false) {
                            $urlParts = explode('?delComment=', $formUrl);
                            $deleteUrlBase = $urlParts[0] . "?delComment=";
                        } else {
                            $deleteUrlBase = $formUrl . "?delComment=";
                        }
                        ?>

                        <form method="post" accept-charset="utf-8" action="<?php echo $formUrl ?>" id="commentForm">

                            <?php if($login::userIsAtLeast($roles::$editor)){ ?>


                                <div id="comment0" class="commentBox" style="display:none;">
                                    <label for="projectStatus" style="display:inline"><?=$this->__('label.project_status_is') ?></label>
                                    <select name="status" id="projectStatus" style="margin-left: 0px; margin-bottom:10px;">
                                        <option value="green"><?=$this->__('label.green') ?></option>
                                        <option value="yellow"><?=$this->__('label.yellow') ?></option>
                                        <option value="red"><?=$this->__('label.red') ?></option>
                                    </select>
                                    <div class="commentReply">

                                        <textarea rows="5" cols="50" class="tinymceSimple" name="text" style="width:100%;"></textarea>
                                        <input type="submit" value="<?php echo $this->__('buttons.save') ?>" name="comment" class="btn btn-primary btn-success" style="margin-left: 0px;"/>
                                        <a href="javascript:void(0)" onclick="toggleCommentBoxes(-1)" style="line-height: 50px;"><?=$this->__('links.cancel');?></a>

                                    </div>
                                    <input type="hidden" name="comment" value="1"/>
                                    <input type="hidden" name="father" id="father" value="0"/>
                                    <br/>
                                </div>
                            <?php } ?>

                            <div id="comments">
                                <div>
                                    <?php
                                        $i = 0;
                                        foreach ($this->get('comments') as $row): ?>
                                        <?php $i++; ?>

                                        <?php if($i==3) {?>
                                            <a href="javascript:void(0)" onclick="jQuery('.readMore').toggle('fast');"><?=$this->__('links.read_more'); ?></a>

                                            <div class="readMore" style="display:none; margin-top:20px;">
                                                <?php } ?>
                                        <div class="clearall">

                                            <div class="">
                                                <div class="commentContent statusUpdate commentStatus-<?=$this->escape($row['status']); ?>">
                                                    <h3 class="">
                                                        <?php printf( $this->__('text.report_written_on'), $this->getFormattedDateString($row['date']),
                                                            $this->getFormattedTimeString($row['date']) ); ?>

                                                        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                                            <div class="inlineDropDownContainer" style="float:right; margin-left:10px;">
                                                                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                                </a>

                                                                <ul class="dropdown-menu">
                                                                    <?php if ($row['userId'] == $_SESSION['userdata']['id']) { ?>
                                                                        <li><a href="<?php echo $deleteUrlBase . $row['id'] ?>" class="deleteComment">
                                                                                <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                                                            </a></li>
                                                                    <?php } ?>
                                                                    <?php
                                                                    if(isset($this->get('ticket')->id)){?>
                                                                        <li><a href="javascript:void(0);" onclick="leantime.ticketsController.addCommentTimesheetContent(<?=$row['id'] ?>, <?=$this->get('ticket')->id ?>);"><?=$this->__("links.add_to_timesheets"); ?></a></li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        <?php } ?>
                                                    </h3>

                                                    <div class="text" id="commentText-<?=$row['id']?>"><?php echo ($row['text']); ?></div>


                                                </div>

                                                <div class="commentLinks">
                                                    <small class="right">
                                                        <?php printf( $this->__('text.written_on_by'), $this->getFormattedDateString($row['date']),
                                                            $this->getFormattedTimeString($row['date']), $this->escape($row['firstname']), $this->escape($row['lastname']) ); ?>
                                                    </small>
                                                    <?php if($login::userIsAtLeast($roles::$commenter)){ ?>
                                                        <a href="javascript:void(0);"
                                                           onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                                                            <span class="fa fa-reply"></span> <?php echo $this->__('links.reply') ?>
                                                        </a>
                                                    <?php } ?>
                                                </div>

                                                <div class="replies">
                                                    <?php if ($comments->getReplies($row['id'])) : ?>
                                                        <?php foreach ($comments->getReplies($row['id']) as $comment): ?>
                                                            <div>
                                                                <div class="commentImage">
                                                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $comment['profileId'] ?>"/>
                                                                </div>
                                                                <div class="commentMain">
                                                                    <div class="commentContent">
                                                                        <div class="right commentDate">
                                                                            <?php printf( $this->__('text.written_on'), $this->getFormattedDateString($comment['date']),
                                                                                $this->getFormattedTimeString($comment['date']) ); ?>
                                                                        </div>
                                                                        <span class="name"><?php printf( $this->__('text.full_name'), $this->escape($comment['firstname']), $this->escape($comment['lastname'])); ?></span>
                                                                        <div class="text"><?php echo ($comment['text']); ?></div>
                                                                    </div>

                                                                    <div class="commentLinks">
                                                                        <?php if($login::userIsAtLeast($roles::$commenter)){ ?>
                                                                            <a href="javascript:void(0);"
                                                                               onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                                                                                <span class="fa fa-reply"></span> <?php echo $this->__('links.reply') ?>
                                                                            </a>
                                                                            <?php if ($comment['userId'] == $_SESSION['userdata']['id']) { ?>
                                                                                <a href="<?php echo $deleteUrlBase . $comment['id'] ?>"
                                                                                   class="deleteComment">
                                                                                    <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                                                                </a>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    </div>
                                                                </div>
                                                                <div class="clearall"></div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    <div style="display:none;" id="comment<?php echo $row['id']; ?>" class="commentBox">
                                                        <div class="commentImage">
                                                            <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $_SESSION['userdata']['profileId'] ?>"/>
                                                        </div>
                                                        <div class="commentReply">
                                                            <input type="submit" value="<?php echo $this->__('links.reply') ?>" name="comment" class="btn btn-default"/>
                                                        </div>
                                                        <div class="clearall"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if(count($this->get('comments')) >= 3){ ?>
                                            </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if(count($this->get('comments')) == 0){ ?>
                                <div style="padding-left:0px;">
                                    <?php echo $this->__('text.no_updates') ?>
                                </div>
                            <?php } ?>
                            <div class="clearall"></div>
                        </form>

                        <script type='text/javascript'>

                            leantime.generalController.initSimpleEditor();

                            function toggleCommentBoxes(id) {

                                <?php if($login::userIsAtLeast($roles::$commenter)){ ?>

                                if (id == 0) {
                                    jQuery('#mainToggler').hide();
                                } else {
                                    jQuery('#mainToggler').show();
                                }
                                jQuery('.commentBox textarea').remove();

                                jQuery('.commentBox').hide('fast', function () {});

                                jQuery('#comment' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" class="tinymceSimple"></textarea>');
                                leantime.generalController.initSimpleEditor();

                                jQuery('#comment' + id + '').show('fast');
                                jQuery('#father').val(id);

                                <?php } ?>

                            }
                        </script>

                    </form>
                </div>

                <div class="maincontentinner">
                    <div class="row" id="projectProgressContainer">
                        <div class="col-md-12">

                            <h5 class="subtitle"><?=$this->__("subtitles.project_progress")?></h5>

                            <div id="canvas-holder" style="width:100%; height:250px;">
                                <canvas id="chart-area" ></canvas>
                            </div>
                            <br /><br />
                        </div>
                    </div>
                    <div class="row" id="milestoneProgressContainer">
                        <div class="col-md-12">
                            <h5 class="subtitle"><?=$this->__("headline.milestones") ?></h5>
                            <ul class="sortableTicketList" >
                                <?php
                                if(count($this->get('milestones')) == 0){
                                    echo"<div class='center'><br /><h4>".$this->__("headlines.no_milestones")."</h4>
                                    ".$this->__("text.milestones_help_organize_projects")."<br /><br /><a href='".BASE_URL."/tickets/roadmap'>".$this->__("links.goto_milestones")."</a>";
                                }
                                ?>
                                <?php foreach($this->get('milestones') as $row){
                                    $percent = 0;


                                    if($row->editTo == "0000-00-00 00:00:00") {
                                        $date = $this->__("text.no_date_defined");
                                    }else {
                                        $date = new DateTime($row->editTo);
                                        $date= $date->format($this->__("language.dateformat"));
                                    }
                                    if($row->percentDone < 100 || $date >= new DateTime()) {
                                        ?>
                                        <li class="ui-state-default" id="milestone_<?php echo $row->id; ?>" >
                                            <div class="ticketBox fixed">

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <strong><a href="<?=BASE_URL ?>/tickets/editMilestone/<?php echo $row->id;?>" class="milestoneModal"><?php $this->e($row->headline); ?></a></strong>
                                                    </div>
                                                </div>
                                                <div class="row">

                                                    <div class="col-md-7">
                                                        <?=$this->__("label.due") ?>
                                                        <?php echo $date; ?>
                                                    </div>
                                                    <div class="col-md-5" style="text-align:right">
                                                        <?=sprintf($this->__("text.percent_complete"), $row->percentDone)?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="progress">
                                                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row->percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row->percentDone; ?>%">
                                                                <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row->percentDone)?></span>
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



       <?php if($login::userIsAtLeast($roles::$editor)) { ?>


           leantime.dashboardController.prepareHiddenDueDate();
           leantime.ticketsController.initEffortDropdown();
           leantime.ticketsController.initMilestoneDropdown();
           leantime.ticketsController.initStatusDropdown();
           leantime.ticketsController.initDueDateTimePickers();

       <?php }else{ ?>

            leantime.generalController.makeInputReadonly(".maincontentinner");
       <?php } ?>

       leantime.dashboardController.initProgressChart("chart-area", <?php echo round($projectProgress['percent']); ?>, <?php echo round((100 - $projectProgress['percent'])); ?>);

       <?php if($sprintBurndown != []){ ?>

           //leantime.dashboardController.initBurndown([<?php foreach($sprintBurndown as $value) echo "'".$value['date']."',"; ?>], [<?php foreach($sprintBurndown as $value) echo "'".round($value['plannedNum'], 2)."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; }  ?> ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedHours']."',"; ?>], [ <?php foreach($sprintBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; }  ?> ]);
           leantime.dashboardController.initChartButtonClick('EffortChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedEffort']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; }  ?> ]);
           leantime.dashboardController.initChartButtonClick('NumChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedNum']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; }  ?> ]);

       <?php } ?>

       <?php if($backlogBurndown != []){ ?>

           //leantime.dashboardController.initBacklogBurndown([<?php foreach($backlogBurndown as $value) echo "'".$value['date']."',"; ?>], [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; }  ?> ]);

           //leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButton', [ <?php foreach($backlogBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; }  ?> ]);
           //leantime.dashboardController.initBacklogChartButtonClick('EffortChartButton', [ <?php foreach($backlogBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; }  ?> ]);
           //leantime.dashboardController.initBacklogChartButtonClick('NumChartButton', [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; }  ?> ]);

       <?php } ?>

    });

</script>
