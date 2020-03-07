<?php  
	$helper = $this->get('helper'); 
	$states = $this->get('states');
    $projectProgress = $this->get('projectProgress');
    $projectProgress = $this->get('projectProgress');
    $sprintBurndown = $this->get('sprintBurndown');
    $backlogBurndown = $this->get('backlogBurndown');

    $ticketsRepo = $this->get('ticketsRepo');
    $efforts = $this->get('efforts');

?>

		<div class="pageheader">
            <div class="pageicon"><span class="fa fa-home"></span></div>
            <div class="pagetitle">

                <div class="row">
                    <div class="col-lg-8">
                        <h5><?php $this->e($_SESSION["currentProjectClient"]); ?></h5>
                        <h1>Project: <?php $this->e($this->get('currentProjectName')); ?></h1>
                    </div>
                    <div class="col-lg-4" style="text-align:right;padding-top:15px">
                        <?php if(count($this->get('allUsers')) == 1) {?>

                                <a href="<?=BASE_URL ?>/users/newUser/" >
                                    <i class="fa fa-users" style="font-size:25px; margin-right:10px; vertical-align: middle"></i>
                                    <span style="font-size:14px; line-height:25px;">Don’t do it alone! Add more users here!</span>
                                </a>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="row" id="sprintBurndownChart">
                            <div class="col-md-12">



                                <?php if($sprintBurndown != []){ ?>

                                    <h5 class="subtitle">Sprint Burndown</h5>

                                    <div class="pull-right">
                                        <div class="btn-group mt-1 mx-auto" role="group">
                                            <a href="javascript:void(0)" id="NumChartButton" class="btn btn-sm btn-secondary active"># of To-Dos</a>
                                            <a href="javascript:void(0)" id="EffortChartButton" class="btn btn-sm btn-secondary ">Effort</a>
                                            <a href="javascript:void(0)" id="HourlyChartButton" class="btn btn-sm btn-secondary">Hourly</a>


                                        </div>

                                    </div>
                                    <?php
                                    $dates = date("m/d/Y", strtotime($this->get('currentSprint')->startDate)) ." - " .date("m/d/Y", strtotime($this->get('currentSprint')->endDate));
                                    echo "<h4 class='inline'>".$this->get('currentSprint')->name."</h4> - ".$dates; ?>
                                    <div style="width:100%; height:350px;">
                                        <canvas id="sprintBurndown"></canvas>
                                    </div>
                                    <div style="float:clear;"></div>
                                    <br /><br /><br />


                                <?php }else if($backlogBurndown != []) { ?>

                                    <h5 class="subtitle">Backlog Burndown</h5>

                                    <div class="pull-right">
                                        <div class="btn-group mt-1 mx-auto" role="group">
                                            <a href="javascript:void(0)" id="NumChartButton"
                                               class="btn btn-sm btn-secondary active"># of To-Dos</a>
                                            <a href="javascript:void(0)" id="EffortChartButton"
                                               class="btn btn-sm btn-secondary ">Effort</a>
                                            <a href="javascript:void(0)" id="HourlyChartButton"
                                               class="btn btn-sm btn-secondary">Hourly</a>


                                        </div>

                                    </div>
                                    <div style="width:100%; height:350px;">
                                        <canvas id="backlogBurndown"></canvas>
                                    </div>
                                    <div style="float:clear;"></div>
                                    <br /><br /><br />
                                    <?php
                                }else {
                                    if ($this->get('upcomingSprint') == false) {
                                        echo "<div class='emptyChartContainer'>
                                        <h4>You don't have an active Sprint!</h4>
                                        A sprint is a short iteration during which To-Dos are completed and released.<br /><br />
                                        <a href='".BASE_URL."/sprints/editSprint' class=\"sprintModal btn btn-primary\" class=''><span class=\"fa fa-rocket\"></span> Create a new Sprint</a>
                                        </div>";
                                    } else {
                                        echo "<div class='emptyChartContainer'>
                                        <h4>Your sprint starts on <strong>" . date("m/d/Y", strtotime($this->get('upcomingSprint')->startDate)) . "</strong> </h4>                                        
                                        This chart will be updated then. In the meantime start adding To-Dos to your sprint.<br /><br />
                                        <a href='".BASE_URL."/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Go to your Backlog</a>
                                        </div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="row" id="yourToDoContainer">
                            <div class="col-md-12">
                                <h5 class="subtitle">To-Dos this Week (<span class="thisWeekCounter"><?php echo count($this->get('tickets')["thisWeek"]); ?></span>)</h5>


                                <ul class="sortableTicketList" >
                                    <li class="">
                                        <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link"  onclick="jQuery('#ticket_new').toggle('fast'); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> Quick Add To-Do</a>
                                        <div class="ticketBox hideOnLoad" id="ticket_new" style="text-align:center;">

                                            <form method="post" class="form-group">
                                                <input name="headline" type="text" style="width:30%;" />
                                                <input type="submit" value="Save" name="quickadd" style="margin-top:-1px;" />
                                                <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                                                <input type="hidden" name="status" value="3" />
                                                <input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />
                                                <a href="javascript:void(0);" class="delete" onclick="jQuery('#ticket_new').toggle('fast'); jQuery('#ticket_new_link').toggle('fast');">
                                                    <i class="fas fa-times"></i> Cancel
                                                </a>
                                            </form>

                                            <div class="clearfix"></div>
                                        </div>
                                    </li>
                                    <?php
                                    if(count($this->get('tickets')["thisWeek"]) == 0){
                                        echo"<div class='center'><br /><h4>You don't have anymore To-Dos for this week!</h4>Take the day off or start working through the backlog.<br/><br/><h4><a href='".BASE_URL."/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Go to your Backlog</a>";
                                    }
                                    ?>
                                    <?php foreach($this->get('tickets')["thisWeek"] as $row){

                                        if($row['dateToFinish'] == "0000-00-00 00:00:00") {
                                            $date = "Anytime";

                                        }else {
                                            $date = new DateTime($row['dateToFinish']);
                                            $date = $date->format("m/d/Y");
                                        }
                                        ?>
                                            <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                                                <div class="ticketBox fixed" data-val="<?php echo $row['id']; ?>">
                                                    <div class="row">
                                                        <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo $row['id'];?>">
                                                            <strong><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row['id'];?>" ><?php $this->e($row['headline']); ?></a></strong>

                                                            <?php

                                                            if ($_SESSION['userdata']['role'] !== 'user') {
                                                                $clockedIn = $this->get("onTheClock");
                                                            ?>

                                                            <div class="inlineDropDownContainer">
                                                                <a href="<?=BASE_URL ?>/users/editOwn/" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="nav-header">To-Do</li>
                                                                    <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"]; ?>"><i class="fa fa-edit"></i> Edit To-Do</a></li>
                                                                    <?php if($_SESSION['userdata']['role'] != "user"){?><li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> Delete To-Do</a></li><?php } ?>
                                                                    <li class="nav-header border">Track Time</li>
                                                                    <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                        <a class="punchIn" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="iconfa-time"></span> Start Work</a>

                                                                        <a class="punchOut" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="iconfa-stop"></span> Stop Work, <?php echo "<span >started at: <span class='time'>".date("h:i A", $clockedIn["since"]); ?></span></span></a>

                                                                        <span class='working' <?php if($clockedIn === false || $clockedIn["id"] === $row["id"]) { echo"style='display:none;'"; }?>>Timer set on another To-Do</span>



                                                                    </li>
                                                                </ul>
                                                            </div>

                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4" style="padding:0px 15px;">
                                                            Due: <input type="text" value="<?php echo $date ?>" class="quickDueDates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                                                        </div>


                                                        <div class="col-md-8" style="padding-top:3px;" >

                                                            <div class="right">


                                                                <a class="effortPopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id']."' value='' data-label='Effort not clear' style='float:left; margin-right:10px;' "; if($row['storypoints'] == '') echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id']."'>Effort not clear</label>";foreach($this->get('efforts') as $effortKey => $effortValue){ echo"<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id'].$effortKey."' value='".$effortKey."' data-label='".$effortValue."' style='float:left; margin-right:10px;' "; if($row['storypoints'] == $effortKey) echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="How big is this ToDo?">
                                                                  <span id="effort-<?php echo $row['id'] ?>" class="f-left  label-default effort" >
                                                                           <?php
                                                                           if($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                                               echo $efforts[$row['storypoints']];
                                                                           }else{
                                                                               echo "?";
                                                                           }?>
                                                                    </span>
                                                                </a>

                                                                <?php if($row['dependingTicketId'] != "" && $row['dependingTicketId'] != 0){?>

                                                                    <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".htmlentities($milestone->headline)."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                                      <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                                               <?php $this->e($row['milestoneHeadline']); ?>
                                                                        </span>
                                                                    </a>

                                                                <?php }else{ ?>

                                                                    <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".htmlentities($milestone->headline)."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                                        <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint">
                                                                                   No Milestone
                                                                            </span>
                                                                    </a>

                                                                <?php } ?>

                                                                <a class="popoverbtn" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php foreach($this->get('allTicketStates') as $key => $statusRow){ echo"<input type='radio' name='ticketStatusChange_".$row['id']."' id='ticketStatusChange".$row['id'].$key."' value='".$key."' style='float:left; margin-right:10px;' "; if($row['status'] == $key) echo" checked='selected' "; echo"><label for='ticketStatusChange".$row['id'].$key."'>". $ticketsRepo->stateLabels[$statusRow]."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Status">
                                                                  <span id="status-<?php echo $row['id'] ?>" class="f-left <?php echo strtolower($ticketsRepo->getStatus($row['status']));?>">
                                                                            <?php echo  $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain($row['status'])]; ?>
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </li>
                                        <?php
                                    } ?>
                                </ul>




                                <br /><br />
                                <h5 class="subtitle">To-Dos Later (<span class="laterCounter"><?php echo count($this->get('tickets')["later"]); ?></span>)</h5>


                                <ul class="sortableTicketList" >

                                    <?php
                                    if(count($this->get('tickets')["later"]) == 0){
                                        echo"<div class='center'><br /><h4>You don't have any To-Dos yet!</h4>Start filling your backlog and assign a few To-Dos to yourself.<br/><br/><h4><a href='".BASE_URL."/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Go to your Backlog</a>";
                                    }
                                    ?>
                                    <?php foreach($this->get('tickets')["later"] as $row){

                                        if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                            $date = "Anytime";

                                        }else {
                                            $date = new DateTime($row['dateToFinish']);
                                            $date = $date->format("m/d/Y");
                                        }
                                        ?>
                                        <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                                            <div class="ticketBox fixed" data-val="<?php echo $row['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo $row['id'];?>">
                                                        <strong><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row['id'];?>" ><?php $this->e($row['headline']); ?></a></strong>

                                                        <?php
                                                        $clockedIn = $this->get("onTheClock");

                                                        ?>


                                                        <div class="inlineDropDownContainer">
                                                            <a href="<?=BASE_URL ?>/users/editOwn/" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li class="nav-header">To-Do</li>
                                                                <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"]; ?>"><i class="fa fa-edit"></i> Edit To-Do</a></li>
                                                                <?php if($_SESSION['userdata']['role'] != "user"){?><li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> Delete To-Do</a></li><?php } ?>
                                                                <li class="nav-header border">Track Time</li>
                                                                <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                                                    <a class="punchIn" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="iconfa-time"></span> Start Work</a>

                                                                    <a class="punchOut" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="iconfa-stop"></span> Stop Work, <?php echo "<span >started at: <span class='time'>".date("h:i A", $clockedIn["since"]); ?></span></span></a>

                                                                    <span class='working' <?php if($clockedIn === false || $clockedIn["id"] === $row["id"]) { echo"style='display:none;'"; }?>>Timer set on another To-Do</span>



                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4" style="padding:0px 15px;">
                                                        Due: <input type="text" value="<?php echo $date ?>" class="quickDueDates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                                                    </div>


                                                    <div class="col-md-8" style="padding-top:3px;" >

                                                        <div class="right">


                                                            <a class="effortPopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id']."' value='' data-label='Effort not clear' style='float:left; margin-right:10px;' "; if($row['storypoints'] == '') echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id']."'>Effort not clear</label>";foreach($this->get('efforts') as $effortKey => $effortValue){ echo"<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id'].$effortKey."' value='".$effortKey."' data-label='".$effortValue."' style='float:left; margin-right:10px;' "; if($row['storypoints'] == $effortKey) echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="How big is this ToDo?">
                                                                  <span id="effort-<?php echo $row['id'] ?>" class="f-left  label-default effort" >
                                                                           <?php
                                                                           if($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                                               echo $efforts[$row['storypoints']];
                                                                           }else{
                                                                               echo "?";
                                                                           }?>
                                                                    </span>
                                                            </a>

                                                            <?php if($row['dependingTicketId'] != "" && $row['dependingTicketId'] != 0){?>

                                                                <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".htmlentities($milestone->headline)."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                                      <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                                               <?php $this->e($row['milestoneHeadline']); ?>
                                                                        </span>
                                                                </a>

                                                            <?php }else{ ?>

                                                                <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".htmlentities($milestone->headline)."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                                        <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint">
                                                                                   No Milestone
                                                                            </span>
                                                                </a>

                                                            <?php } ?>

                                                            <a class="popoverbtn" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php foreach($this->get('allTicketStates') as $key => $statusRow){ echo"<input type='radio' name='ticketStatusChange_".$row['id']."' id='ticketStatusChange".$row['id'].$key."' value='".$key."' style='float:left; margin-right:10px;' "; if($row['status'] == $key) echo" checked='selected' "; echo"><label for='ticketStatusChange".$row['id'].$key."'>". $ticketsRepo->stateLabels[$statusRow]."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Status">
                                                                  <span id="status-<?php echo $row['id'] ?>" class="f-left <?php echo strtolower($ticketsRepo->getStatus($row['status']));?>">
                                                                            <?php echo  $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain($row['status'])]; ?>
                                                                    </span>
                                                            </a>
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


                                <h5 class="subtitle">Project Progress</h5>

                                <div id="canvas-holder" style="width:100%; height:250px;">
                                    <canvas id="chart-area" ></canvas>
                                </div>
                                <div style="text-align:center">
                                    <span>Estimated Date of Completion:</span><br /><h4><?php echo $projectProgress['estimatedCompletionDate']; ?></h4>
                                </div>
                                <br /><br />
                            </div>
                        </div>
                        <div class="row" id="milestoneProgressContainer">
                            <div class="col-md-12">
                                <h5 class="subtitle">Milestone Progress</h5>
                                <ul class="sortableTicketList" >
                                    <?php
                                    if(count($this->get('milestones')) == 0){
                                        echo"<div class='center'><br /><h4>You don't have any Milestones yet!</h4>
                                        Milestones organize your Project into larger stages. <br />Start planning your roadmap<br /><br /><a href='".BASE_URL."/tickets/roadmap' class='btn btn-primary'><span class=\"fas fa-map\"></span> Go to Roadmap Planner</a>";

                                    }
                                    ?>
                                    <?php foreach($this->get('milestones') as $row){
                                        $percent = 0;

                                        if($row->allTickets != 0 ) {
                                            $percent = round(($row->doneTickets/$row->allTickets)*100);
                                        }
                                        if($row->editTo == "0000-00-00 00:00:00") {
                                            $date = "No Date defined";
                                        }else {
                                            $date = new DateTime($row->editTo);
                                            $date= $date->format("m/d/Y");
                                        }
                                        if($percent < 100 || $date >= new DateTime()) {
                                            ?>
                                            <li class="ui-state-default" id="milestone_<?php echo $row->id; ?>" >
                                                <div class="ticketBox fixed">

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <strong><a href="<?=BASE_URL ?>/tickets/showKanban&milestone=<?php echo $row->id;?>" ><?php $this->e($row->headline); ?></a></strong>
                                                        </div>
                                                    </div>
                                                    <div class="row">

                                                        <div class="col-md-7">
                                                            Due By:
                                                            <?php echo $date; ?>
                                                        </div>
                                                        <div class="col-md-5" style="text-align:right">
                                                            <?php echo $percent; ?>% Complete
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="progress">
                                                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%">
                                                                    <span class="sr-only"><?php echo $percent; ?>% Complete</span>
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

    function colorBoxes(currentBox){

        var color = "#fff";
        jQuery(".ticketBox").each(function(index){

            var value = jQuery(this).find("a.popoverbtn span").attr("class");


            if(value != undefined) {
                if (value.indexOf("important") > -1) {

                    color = "#b94a48";

                } else if (value.indexOf("warning") > -1) {

                    color = "#f89406";

                } else if (value.indexOf("success") > -1) {

                    color = "#468847";

                } else if (value.indexOf("default") > -1) {

                    color = "#999999";
                }
                jQuery(this).css("borderLeft", "5px solid " + color);

                if(jQuery(this).attr("data-val") == currentBox) {
                    jQuery("#ticket_"+currentBox+" .ticketBox").animate({backgroundColor: color}, 'fast').animate({backgroundColor: "#fff"}, 'slow');
                }

            }
        });



    }
    jQuery(function() {

        jQuery('.popoverbtn').popover({
            template: '<div class="popover statusPopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
        });

        colorBoxes();

        jQuery("body").on("click", ".statusPopoverContainer input", function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();


            var ticket = jQuery(this).attr("name").split("_");
            var val = jQuery(this).val();

            var statusPlain = [];
            statusPlain[3] = 'label-important';
            statusPlain[1] = 'label-important';
            statusPlain[4] = 'label-warning';
            statusPlain[2] = 'label-warning';
            statusPlain[0] = 'label-success';
            statusPlain["-1"] = 'label-default';


            var statusContent = [];
            statusContent[3] = '<?php echo $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain(3)]; ?>';
            statusContent[1] = '<?php echo $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain(1)]; ?>';
            statusContent[4] = '<?php echo $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain(4)]; ?>';
            statusContent[2] = '<?php echo $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain(2)]; ?>';
            statusContent[0] = '<?php echo $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain(0)]; ?>';
            statusContent["-1"] = '<?php echo $ticketsRepo->stateLabels[$ticketsRepo->getStatusPlain(-1)]; ?>';

            jQuery.ajax({
                type: 'POST',
                url: leantime.appUrl+'/tickets/showAll&raw=true&changeStatus=true',
                data:
                    {
                        id: ticket[1],
                        status: val
                    }
            });
            jQuery("#status-" + ticket[1]).attr("class", "f-left " + statusPlain[val]);
            jQuery("#status-" + ticket[1]).text(statusContent[val]);
            jQuery('.popoverbtn').popover("hide");
            colorBoxes(ticket[1]);


        });

    });

   jQuery(document).ready(function() {

       var thisFriday = moment().startOf('week').add(5, 'days');

        jQuery("#dateToFinish").val(thisFriday.format("YYYY-MM-DD"));

       leantime.dashboardController.initProgressChart(<?php echo round($projectProgress['percent']); ?>, <?php echo round((100 - $projectProgress['percent'])); ?>);

       <?php if($sprintBurndown != []){ ?>
           leantime.dashboardController.initBurndown([<?php foreach($sprintBurndown as $value) echo "'".$value['date']."',"; ?>], [<?php foreach($sprintBurndown as $value) echo "'".round($value['plannedNum'], 2)."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ])

           leantime.dashboardController.initChartButtonClick('HourlyChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedHours']."',"; ?>], [ <?php foreach($sprintBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ])
           leantime.dashboardController.initChartButtonClick('EffortChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedEffort']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ])
           leantime.dashboardController.initChartButtonClick('NumChartButton', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedNum']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ])

       <?php } ?>


       <?php if($backlogBurndown != []){ ?>
       leantime.dashboardController.initBacklogBurndown([<?php foreach($backlogBurndown as $value) echo "'".$value['date']."',"; ?>], [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ])

       leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButton', [ <?php foreach($backlogBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ])
       leantime.dashboardController.initBacklogChartButtonClick('EffortChartButton', [ <?php foreach($backlogBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ])
       leantime.dashboardController.initBacklogChartButtonClick('NumChartButton', [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ])

       <?php } ?>

       <?php

       if(isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0){     ?>
           leantime.helperController.showHelperModal("dashboard", 500, 700);

       <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["dashboard"] = 1;
       } ?>
    });

</script>





















