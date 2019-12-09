<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$sprints        = $this->get("sprints");
$searchCriteria = $this->get("searchCriteria");
$currentSprint  = $this->get("currentSprint");

$todoTypeIcons  = $this->get("ticketTypeIcons");

$efforts        = $this->get('efforts');
$statusLabels   = $this->get('allTicketStates');

//All states >0 (<1 is archive)
$numberofColumns = count($this->get('allTicketStates'))-1;
$size = floor(100 / $numberofColumns);

?>


  
 <div class="pageheader">           
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
    	<h1><?php echo $this->__('ALL_TICKETS'); ?></h1>
	</div>
</div><!--pageheader-->
           
<div class="maincontent">
	<div class="maincontentinner">
		<form action="/tickets/showAll" method="get">
            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-4">
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-th"></i> &nbsp; Add <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a href="/tickets/newTicket"><span class="iconfa-pushpin"></span> Add ToDo</a></li>
                            <li><a href="/tickets/editMilestone" class="milestoneModal"><span class="fa fa-map"></span> Add Milestone</a></li>
                            <li><a href="/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Add Sprint</a></li>
                        </ul>
                    </div>
                    <a href="javascript:void(0);" onclick="leantime.ticketsController.toggleFilterBar();" class="formLink btn btn-default"><i class="fas fa-filter"></i> Filters</a>
                </div>

                <div class="col-md-4 center">
                    <span class="">
                       <h4 style="font-size:16px;"></h4>
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="pull-right">
                        <div class="btn-group mt-1 mx-auto" role="group">
                            <a href="/tickets/showKanban" class="btn btn-sm btn-secondary "><?=$this->__("links.kanban") ?></a>
                            <a href="/tickets/showAll" class="btn btn-sm btn-secondary active"><?=$this->__("links.table") ?></a>
                        </div>

                    </div>
                </div>

            </div>

            <div class="clearfix"></div>
            <div class="filterBar <?php if($searchCriteria['users'] == '' && $searchCriteria['milestone'] == '' && $searchCriteria['type'] == '') { echo "hideOnLoad"; } ?>">


                <div class="row-fluid">

                    <div class="pull-right">
                        <input type="text" class="form-control input-default" id="searchTerm" name="searchTerm" placeholder="Search" value="<?php echo $searchCriteria['term']; ?>">
                        <input type="submit" value="Search" name="search" class="form-control btn btn-primary" />
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.user") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_user") ?>" title="<?=$this->__("input.placeholders.filter_by_user") ?>" name="users" multiple="multiple" class="user-select" id="userSelect">
                                <option value=""></option>
                                <?php foreach($this->get('users') as $userRow){ 	?>

                                    <?php echo"<option value='".$userRow["id"]."'";

                                    if($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && array_search($userRow["id"], explode(",", $searchCriteria['users'])) !== false) echo" selected='selected' ";

                                    echo">".$this->escape($userRow["firstname"]." ".$userRow["lastname"])."</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>


                    </div>
                    <div class="filterBoxLeft">

                        <label class="inline"><?=$this->__("label.milestone") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" title="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="milestone"  class="user-select" id="milestoneSelect">
                                <option value=""><?=$this->__("label.all_milestones") ?></option>
                                <?php foreach($this->get('milestones') as $milestoneRow){ 	?>

                                    <?php echo"<option value='".$milestoneRow->id."'";

                                    if(isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) echo" selected='selected' ";

                                    echo">".$this->escape($milestoneRow->headline)."</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>

                    </div>

                    <div class="filterBoxLeft">

                        <label class="inline"><?=$this->__("label.todo_type") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" title="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="type" id="typeSelect">
                                <option value=""><?=$this->__("label.all_types") ?></option>
                                <?php foreach($this->get('types') as $type){ 	?>

                                    <?php echo"<option value='".$type."'";

                                    if(isset($searchCriteria['type']) && ($searchCriteria['type'] == $type)) echo" selected='selected' ";

                                    echo">$type</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>

                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline">Status</label>
                        <div class="form-group">

                            <select data-placeholder="Filter by Status..." name="searchStatus[]"  multiple="multiple" class="status-select" onchange="form.submit()">
                                <option value=""></option>
                                <option value="not_done" <?php if($searchCriteria['status'] !== false && strpos($searchCriteria['status'], 'not_done') !== false) echo" selected='selected' ";?>>Not done</option>
                                <?php foreach($this->get('allTicketStates') as $key => $statusRow){ 	?>

                                    <?php echo"<option value='".$key."'";

                                    if($searchCriteria['status'] !== false && strpos($searchCriteria['status'], (string) $key) !== false) echo" selected='selected' ";
                                    echo">". $tickets->stateLabels[$statusRow]."</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>

                    </div>


                </div>

            </div>

            <table id="allTicketsTable" class="table table-bordered display" style="width:100%">
                <colgroup>
                    <col class="con1" width="35%">
                    <col class="con0" width="10%">
                    <col class="con1" width="10%">
                    <col class="con0" width="10%">
                    <col class="con1" width="5%">
                    <col class="con0" width="10%">
                    <col class="con1" width="10%">
                    <col class="con0" width="5%">
                    <col class="con0" width="5%">
                </colgroup>
                <thead>
                <tr>
                    <th><?= $this->__("label.title"); ?></th>
                    <th><?= $this->__("label.todo_status"); ?></th>
                    <th><?= $this->__("label.sprint"); ?></th>
                    <th><?= $this->__("label.milestone"); ?></th>
                    <th><?= $this->__("label.effort"); ?></th>
                    <th><?= $this->__("label.editor"); ?>.</th>
                    <th><?= $this->__("label.due_date"); ?></th>
                    <th><?= $this->__("label.estimate"); ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($this->get('allTickets') as $row){?>
                        <tr>
                            <td><a href="/tickets/showTicket/<?=$this->e($row['id']); ?>"><?=$this->e($row['headline']); ?></a></td>
                            <td>
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
                            </td>
                            <td></td>
                            <td data-order="<?=$this->e($row['milestoneHeadline'])?>">
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
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <div class="dropdown ticketDropdown userDropdown noBg show ">
                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text">
                                                                    <?php if($row["editorFirstname"] != ""){
                                                                        echo "<span id='userImage".$row['id']."'><img src='/api/users?profileImage=".$row['editorProfileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row['id']."'> ". $this->escape($row["editorFirstname"]). "</span>";
                                                                    }else {
                                                                        echo "<span id='userImage".$row['id']."'><img src='/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row['id']."'>".$this->__("dropdown.not_assigned")."</span> <i class=\"fas fa-caret-down\"></i>";
                                                                    }?>
                                                                </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                        <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                        <?php foreach($this->get('users') as $user){
                                            echo"<li class='dropdown-item'>
                                                                <a href='javascript:void(0);' data-label='".$this->escape($user['firstname']." ".$user['lastname'])."' data-value='".$row['id']."_".$user['id']."_".$user['profileId']."' id='userStatusChange".$row['id'].$user['id']."' ><img src='/api/users?profileImage=".$user['profileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".$this->escape($user['firstname']." ".$user['lastname'])."</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <?php
                                if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                $date = $this->__("text.anytime");

                                }else {
                                $date = new DateTime($row['dateToFinish']);
                                $date = $date->format($this->__("language.dateformat"));

                                }

                                echo $this->__("label.due_icon"); ?><input type="text" title="<?php echo $this->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />

                            </td>
                            <td><input type="text" value="<?=$this->e($row['planHours']); ?>" name="planHours" class="small-input"/></td>
                            <td></td>
                        </tr>

                    <?php } ?>
                </tbody>

            </table>

            <?php /*

            <div class="row">
                <div class="col-md-4">
                    <h4 style="padding-top: 15px;"><span id="countSprintItems"><?=count($this->get('allSprintTickets')) ?></span> To-Dos in your sprint</h4>
                </div>

                <div class="col-md-4 center">
                    <span class="currentSprint">

                        <?php
                        $dates = "";
                        if($this->get('sprints') === false || count($this->get('sprints')) == 0) { echo "<br/><h4>Start your first Sprint!</h4>"; } ?>
                        <?php if($this->get('sprints') !== false){?>
                        <select data-placeholder="Filter by Sprint..." name="searchSprints" class="mainSprintSelector" onchange="form.submit()">

                            <?php

                            foreach($this->get('sprints') as $sprintRow){ 	?>

                                <?php echo"<option value='".$sprintRow->id."'";

                                if($this->get("currentSprint") !== false && $sprintRow->id == $this->get("currentSprint")) {
                                    echo " selected='selected' ";
                                    $dates = date("m/d/Y", strtotime($sprintRow->startDate)) ." - " .date("m/d/Y", strtotime($sprintRow->endDate));
                                }
                                echo">".$sprintRow->name."</option>"; ?>

                            <?php } 	?>
                            </select>

                            <br/><?php } ?>
                        <small>
                            <?php if($dates != "") {
                                echo $dates; ?> - <a href="/sprints/editSprint/<?=$this->get("currentSprint")?>" class="sprintModal">edit Sprint</a>
                            <?php }else{ ?>
                                <a href="/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Create a new Sprint</a>
                            <?php } ?>
                        </small>
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="pull-right">
                        <div class="btn-group mt-1 mx-auto" role="group">
                            <a href="/tickets/showKanban" class="btn btn-sm btn-secondary "><i class="fas fa-columns"></i> Kanban</a>
                            <a href="/tickets/showAll" class="btn btn-sm btn-secondary active"><i class='iconfa-list'></i> List</a>
                        </div>
                    </div>
                </div>

            </div>

            <ul id="sortableSprint" class="sortableTicketList" data-sprint="<?php if($this->get("currentSprint") ){echo $this->get("currentSprint"); } ?>">
                <?php
                if(count($this->get('allSprintTickets')) == 0){
                    echo"<div class='empty' id='emptySprint'><h1><i class=\"fas fa-rocket\"></i></h1><h4>You don't have anything in your sprint yet.<br/> Drag a few To-Dos from your backlog into this sprint</h4></div>";
                }
                $i =0;
                foreach($this->get('allSprintTickets') as $row) { ?>
                    <li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
                        <div class="ticketBox">
                            <?php if ($_SESSION['userdata']['role'] !== 'user') {
                                $clockedIn = $this->get("onTheClock");
                                ?>
                                <div class="inlineDropDownContainer" style="float:right;">

                                    <a href="/users/editOwn/" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="nav-header">To-Do</li>
                                        <li><a href="/tickets/showTicket/<?php echo $row["id"]; ?>"><i class="fa fa-edit"></i> Edit To-Do</a></li>
                                        <li><a href="/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> Delete To-Do</a></li>
                                        <li class="nav-header border">Track Time</li>
                                        <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                            <a class="punchIn" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="iconfa-time"></span> Start Work</a>

                                            <a class="punchOut" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="iconfa-stop"></span> Stop Work, <?php echo "<span >started at: <span class='time'>".date("h:i A", $clockedIn["since"]); ?></span></span></a>

                                            <span class='working' <?php if($clockedIn === false || $clockedIn["id"] === $row["id"]) { echo"style='display:none;'"; }?>>Timer set on another To-Do</span>



                                        </li>
                                    </ul>
                                </div>
                            <?php } ?>


                            <div class="right" style="float:right;">



                            </div>
                            <small><i class="fa <?php echo $todoTypeIcons[$row['type']]; ?>"></i> <?php echo $row['type']; ?></small>
                            <h3><a href="/tickets/showTicket/<?php echo $row["id"];?>" >#<?php echo $row["id"];?> - <?php echo $row["headline"];?></a></h3>
                            <p><?php echo substr(strip_tags($row["description"]), 0, 150);?><?php if(strlen($row["description"]) > 0) echo"(...)";?></p>

                            <div class="clearfix"></div>

                            <div class="pull-left" style="margin-top:10px;">
                                <?php
                                if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                    $date = "Anytime";

                                }else {
                                    $date = new DateTime($row['dateToFinish']);
                                    $date = $date->format("m/d/Y");
                                }
                                ?>

                                Due: <input type="text" value="<?php echo $date ?>" class="quickDueDates secretInput" data-id="<?php echo $row['id'];?>" name="quickDueDate" />


                                <a href="/tickets/showTicket/<?php echo $row["id"];?>#comment" ><span class="iconfa-comments"></span><?php echo $row["commentCount"] ?> Comments</a>
                                &nbsp;&nbsp;<a href="/tickets/showTicket/<?php echo $row["id"];?>#files"><span class="iconfa-paper-clip"></span><?php echo $row["fileCount"] ?> Files</a>
                                <?php

                                if ($row["tags"] != "" && count($row["tags"]) > 0){?>
                                    <i class="iconfa-tags"></i> <?php echo str_replace(",", ", ", $row["tags"]) ?>
                                <?php } ?>
                                <a class="userPopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketUserChange_".$row['id']."' id='ticketUserChange".$row['id']."' value='' data-label='nobody' style='float:left; margin-right:10px;' "; if($row['editorId'] == '') echo" checked='selected' "; echo"><label for='ticketUserChange".$row['id']."'>Not assigned</label>";foreach($this->get('users') as $user){ echo"<input type='radio' name='ticketUserChange_".$row['id']."' id='ticketUserChange".$row['id'].$user['id']."' value='".$user['id']."' data-label='".$user['firstname']."' style='float:left; margin-right:10px;' "; if($row['editorId'] == $user['id']) echo" checked='selected' "; echo"><label for='ticketUserChange".$row['id'].$user['id']."'>".$user['firstname'].", ".$user['lastname']."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Who is working on this?">
                                            <span class="author"><span class="iconfa-user"></span>
                                                <?php if($row["editorFirstname"] != ""){
                                                    echo "Assigned to <span id='user".$row['id']."'> ". $row["editorFirstname"]. "</span> <i class=\"fas fa-caret-down\"></i>";
                                                }else {
                                                    echo "Assigned to <span id='user".$row['id']."'>nobody</span> <i class=\"fas fa-caret-down\"></i>";
                                                }?>
                                            </span>
                                </a>
                                &nbsp;



                            </div>
                            <div class="right timerContainer" style="float:right; " id="timerContainer-<?php echo $row["id"]; ?>" >

                                <?php /*
                                $clockedIn = $this->get("onTheClock");
                                ?>
                                <button class="btn btn-default punchIn" value="<?php echo $row["id"]; ?>"
                                    <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>
                                ><span class="iconfa-time"></span> Start Work</button>

                                <span class="working"
                                    <?php if($clockedIn["id"] != $row["id"]) {?>
                                        style="display:none"
                                    <?php } ?>
                                >

								<?php echo "<span class='btn btn-white'>Started at: <span class='time'>".date("h:i A", $clockedIn["since"]); ?></span></span>
                                <button class="btn btn-default punchOut" value="<?php echo $row["id"]; ?>"><span class="iconfa-stop"></span> Stop Work</span></button>

                                </span>

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

                                    <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                  <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                           <?php echo $row['milestoneHeadline']; ?>
                                                    </span>
                                    </a>

                                <?php }else{ ?>

                                    <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint">
                                                           No Milestone
                                                    </span>
                                    </a>

                                <?php } ?>




                                <a class="popoverbtn" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php foreach($this->get('allTicketStates') as $key => $statusRow){ echo"<input type='radio' name='ticketStatusChange_".$row['id']."' id='ticketStatusChange".$row['id'].$key."' value='".$key."' style='float:left; margin-right:10px;' "; if($row['status'] == $key) echo" checked='selected' "; echo"><label for='ticketStatusChange".$row['id'].$key."'>".$tickets->stateLabels[$statusRow]."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Status">
                                    <span id="status-<?php echo $row['id'] ?>" class="f-left <?php echo strtolower($tickets->getStatus($row['status']));?>">
                                            <?php echo  $tickets->stateLabels[$tickets->getStatusPlain($row['status'])]; ?>
                                    </span>
                                </a>

                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </li>
                    <?php
                    $i++;
                }
                ?>
            </ul>

            <?php /*
            <div class="clearfix"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-th"></i> &nbsp; Add <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a href="/tickets/newTicket"><span class="iconfa-pushpin"></span> Add ToDo</a></li>
                            <li><a href="/tickets/editMilestone" class="milestoneModal"><span class="fa fa-map"></span> Add Milestone</a></li>
                            <li><a href="/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Add Sprint</a></li>
                        </ul>
                    </div>
                    <a href="javascript:void(0);" onclick="leantime.ticketsController.toggleFilterBar();" class="formLink btn btn-default"><i class="fas fa-filter"></i> Filters</a>
                </div>

                <div class="col-md-4 center">
                    <span class="">
                       <h4 style="font-size:16px;">Backlog</h4>
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="pull-right">

                    </div>
                </div>

            </div>

            <div class="clearfix"></div>
            <div class="filterBar <?php

            if((count($searchCriteria['users']) == 0 || $searchCriteria['users'] == '') && $searchCriteria['milestone'] == '' && $searchCriteria['searchType'] == '') { echo "hideOnLoad"; } ?>">

                <div class="loading"></div>
                <div class="row-fluid" style="opacity:0.4">

                    <div class="pull-right">
                        <input type="text" class="form-control input-default" id="searchTerm" name="searchTerm" placeholder="Search" value="<?php echo $searchCriteria['searchterm']; ?>">
                        <input type="submit" value="Search" name="search" class="form-control btn btn-primary" />
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline">User</label>
                        <div class="form-group">
                            <select data-placeholder="Filter by User..." name="searchUsers[]"  multiple="multiple" class="user-select" onchange="form.submit()">
                                <option value=""></option>
                                <?php foreach($this->get('users') as $userRow){ 	?>

                                    <?php echo"<option value='".$userRow["id"]."'";

                                    if($searchCriteria['users'] !== false && array_search($userRow["id"], $searchCriteria['users']) !== false) echo" selected='selected' ";

                                    echo">".$userRow["firstname"]." ".$userRow["lastname"]."</option>"; ?>

                                <?php } 	?>
                            </select>

                         </div>

                    </div>

                    <div class="filterBoxLeft">

                            <label class="inline">Milestone</label>
                            <div class="form-group">
                                <select data-placeholder="Filter by Milestone..." name="searchMilestone"  class="user-select" onchange="form.submit()">
                                    <option value="">All Milestones</option>
                                    <?php foreach($this->get('milestones') as $milestoneRow){ 	?>

                                        <?php echo"<option value='".$milestoneRow->id."'";

                                        if(isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) echo" selected='selected' ";

                                        echo">".$milestoneRow->headline."</option>"; ?>

                                    <?php } 	?>
                                </select>
                            </div>


                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline">Status</label>
                        <div class="form-group">

                            <select data-placeholder="Filter by Status..." name="searchStatus[]"  multiple="multiple" class="status-select" onchange="form.submit()">
                                <option value=""></option>
                                <option value="not_done" <?php if($searchCriteria['status'] !== false && strpos($searchCriteria['status'], 'not_done') !== false) echo" selected='selected' ";?>>Not done</option>
                                    <?php foreach($this->get('allTicketStates') as $key => $statusRow){ 	?>

                                        <?php echo"<option value='".$key."'";

                                        if($searchCriteria['status'] !== false && strpos($searchCriteria['status'], (string) $key) !== false) echo" selected='selected' ";
                                        echo">". $tickets->stateLabels[$statusRow]."</option>"; ?>

                                    <?php } 	?>
                            </select>
                         </div>

                    </div>

                    <div class="">

                        <label class="inline">To-Do Type</label>
                        <div class="form-group">
                            <select data-placeholder="Filter by Type..." name="searchType" onchange="form.submit()">
                                <option value="">All Types</option>
                                <?php foreach($this->get('types') as $type){ 	?>

                                    <?php echo"<option value='".$type."'";

                                    if(isset($searchCriteria['searchType']) && ($searchCriteria['searchType'] == $type)) echo" selected='selected' ";

                                    echo">$type</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>

                    </div>





                </div>

		    </div>


		    <ul id="sortableBacklog" class="sortableTicketList" data-sprint="">
                <li class="">
                    <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link"  onclick="jQuery('#ticket_new').toggle('fast'); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> Quick Add To-Do</a>
                    <div class="ticketBox hideOnLoad" id="ticket_new" style="text-align:center;">

                        <form method="post">
                            <textarea name="headline" style="width:30%;"></textarea><br />
                            <input type="submit" value="Save" name="quickadd">
                            <input type="hidden" name="milestone" value="<?php echo $searchCriteria['milestone']; ?>" />
                            <input type="hidden" name="status" value="3" />
                            <input type="hidden" name="sprint" value="" />
                            <a href="javascript:void(0);" onclick="jQuery('#ticket_new').toggle('fast'); jQuery('#ticket_new_link').toggle('fast');">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </form>

                        <div class="clearfix"></div>
                    </div>
                </li>
			<?php
            $i =0;
            if(count($this->get('allBacklogTickets')) == 0){
                echo"<div class='empty' id='emptySprint'><h1><i class=\"fas fa-search\"></i></h1><h4>We couldn't find anything in your backlog.</h4></div>";
            }
            foreach($this->get('allBacklogTickets') as $row) { ?>
				<li class="ui-state-default" id="ticket_<?php echo $row['id']; ?>" >
					<div class="ticketBox">
                        <?php if ($_SESSION['userdata']['role'] !== 'user') {
                        $clockedIn = $this->get("onTheClock");
                        ?>
                        <div class="inlineDropDownContainer" style="float:right;">

                            <a href="/users/editOwn/" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="nav-header">To-Do</li>
                                <li><a href="/tickets/showTicket/<?php echo $row["id"]; ?>"><i class="fa fa-edit"></i> Edit To-Do</a></li>
                                <li><a href="/tickets/delTicket/<?php echo $row["id"]; ?>" class="delete"><i class="fa fa-trash"></i> Delete To-Do</a></li>
                                <li class="nav-header border">Track Time</li>
                                <li id="timerContainer-<?php echo $row['id'];?>" class="timerContainer">
                                    <a class="punchIn" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn !== false) { echo"style='display:none;'"; }?>><span class="iconfa-time"></span> Start Work</a>

                                    <a class="punchOut" href="javascript:void(0);" value="<?php echo $row["id"]; ?>" <?php if($clockedIn === false || $clockedIn["id"] != $row["id"]) { echo"style='display:none;'"; }?>><span class="iconfa-stop"></span> Stop Work, <?php echo "<span >started at: <span class='time'>".date("h:i A", $clockedIn["since"]); ?></span></span></a>

                                    <span class='working' <?php if($clockedIn === false || $clockedIn["id"] === $row["id"]) { echo"style='display:none;'"; }?>>Timer set on another To-Do</span>



                                </li>
                            </ul>
                        </div>
                        <?php } ?>
                        <small><i class="fa <?php echo $todoTypeIcons[$row['type']]; ?>"></i> <?php echo $row['type']; ?></small>
						<h3><a href="/tickets/showTicket/<?php echo $row["id"];?>" >#<?php echo $row["id"];?> - <?php echo $row["headline"];?></a></h3>
						<p><?php echo substr(strip_tags($row["description"]), 0, 150);?><?php if(strlen($row["description"]) > 0) echo"(...)";?></p>
						
						<div class="clearfix"></div>
						
						<div class="pull-left" style="margin-top:10px;">
                            <?php
                            if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                $date = "Anytime";

                            }else {
                                $date = new DateTime($row['dateToFinish']);
                                $date = $date->format("m/d/Y");
                            }
                            ?>

                            Due: <input type="text" value="<?php echo $date ?>" class="quickDueDates secretInput" data-id="<?php echo $row['id'];?>" name="quickDueDate" />

                            <a href="/tickets/showTicket/<?php echo $row["id"];?>#comment"><span class="iconfa-comments"></span><?php echo $row["commentCount"] ?> Comments</a>
							<a href="/tickets/showTicket/<?php echo $row["id"];?>#files"><span class="iconfa-paper-clip"></span><?php echo $row["fileCount"] ?> Files</a>
                            <?php if ($row["tags"] != ""  && count($row["tags"]) > 0 ){?>
                                <i class="iconfa-tags"></i> <?php echo str_replace(",", ", ", $row["tags"]) ?>
                            <?php } ?>
                            <a class="userPopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketUserChange_".$row['id']."' id='ticketUserChange".$row['id']."' value='' data-label='nobody' style='float:left; margin-right:10px;' "; if($row['editorId'] == '') echo" checked='selected' "; echo"><label for='ticketUserChange".$row['id']."'>Not assigned</label>";foreach($this->get('users') as $user){ echo"<input type='radio' name='ticketUserChange_".$row['id']."' id='ticketUserChange".$row['id'].$user['id']."' value='".$user['id']."' data-label='".$user['firstname']."' style='float:left; margin-right:10px;' "; if($row['editorId'] == $user['id']) echo" checked='selected' "; echo"><label for='ticketUserChange".$row['id'].$user['id']."'>".$user['firstname'].", ".$user['lastname']."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Who is working on this?">
                                            <span class="author"><span class="iconfa-user"></span>
                                                <?php if($row["editorFirstname"] != ""){
                                                    echo "Assigned to <span id='user".$row['id']."'> ". $row["editorFirstname"]. "</span> <i class=\"fas fa-caret-down\"></i>";
                                                }else {
                                                    echo "Assigned to <span id='user".$row['id']."'>nobody</span> <i class=\"fas fa-caret-down\"></i>";
                                                }?>
                                            </span>
                            </a>


						</div>
						<div class="right timerContainer" style="float:right; margin-top:10px;" id="timerContainer-<?php echo $row["id"]; ?>" >

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

                                <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                  <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                           <?php echo $row['milestoneHeadline']; ?>
                                                    </span>
                                </a>

                            <?php }else{ ?>

                                <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint">
                                                           No Milestone
                                                    </span>
                                </a>

                            <?php } ?>




                            <a class="popoverbtn" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php foreach($this->get('allTicketStates') as $key => $statusRow){ echo"<input type='radio' name='ticketStatusChange_".$row['id']."' id='ticketStatusChange".$row['id'].$key."' value='".$key."' style='float:left; margin-right:10px;' "; if($row['status'] == $key) echo" checked='selected' "; echo"><label for='ticketStatusChange".$row['id'].$key."'>". $tickets->stateLabels[$statusRow]."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Status">
                              <span id="status-<?php echo $row['id'] ?>" class="f-left <?php echo strtolower($tickets->getStatus($row['status']));?>">
										<?php echo  $tickets->stateLabels[$tickets->getStatusPlain($row['status'])]; ?>
								</span>
                            </a>
								
						</div>
						<div class="clearfix"></div>
					</div>
				</li>
				<?php 
				$i++;
			} 
			?>
		    </ul>
            <div class="clearfix"></div>


            */ ?>

        </form>


        <?php
        if(isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1){     ?>
            <p class="align-center"><br /><em>Once you added a few items to your sprint. Go to your Kanban Board</em> <br /><a href="/tickets/showKanban/" class="btn btn-primary"><span class="fas fa-columns"></span> Kanban Board</a></p>
        <?php } ?>

	</div>
</div>

<script type="text/javascript">

    leantime.ticketsController.initUserSelectBox();
    leantime.ticketsController.initStatusSelectBox();
    leantime.ticketsController.initTicketsTable();

    <?php if(isset($_SESSION['userdata']['settings']["modals"]["backlog"]) === false || $_SESSION['userdata']['settings']["modals"]["backlog"] == 0){     ?>
    leantime.helperController.showHelperModal("backlog");
    <?php
    //Only show once per session
    $_SESSION['userdata']['settings']["modals"]["backlog"] = 1;
    } ?>


</script>