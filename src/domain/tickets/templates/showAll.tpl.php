<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$tickets = $this->get("tickets");
$searchCriteria = $this->get("searchCriteria");

$todoTypeIcons = array('Story' => 'fa-book', 'Task' => 'fa-check-square', 'Bug' => 'fa-bug');
$efforts = $this->get('efforts');

?>

 <script type="text/javascript">
 	
    function colorBoxes(){
    	  	
	  	jQuery(".ticketBox").each(function(index){
	  		
	  		var value = jQuery(this).find("a.popoverbtn span").attr("class");
			var color = "#fff";

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
            }
	  	});
    }
    
  
  jQuery(window).bind("load", function () {
  		jQuery(".loading").fadeOut();
        jQuery(".filterBar .row-fluid").css("opacity", "1");
        
  });
      
  jQuery(function() {

 	jQuery('.popoverbtn').popover({
        template:'<div class="popover statusPopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });
 	
 	jQuery("body").on("click", ".statusPopoverContainer input", function(){
 		
 		var ticket = jQuery(this).attr("name").split("_");
 		var val = jQuery(this).val();
 		console.log(ticket[1]);
 		console.log(val);
 		var statusPlain = [];
 		statusPlain[3] = 'label-important';
 		statusPlain[1] = 'label-important';
 		statusPlain[4] = 'label-warning';
 		statusPlain[2] = 'label-warning';
 		statusPlain[0] = 'label-success';
 		statusPlain["-1"] = 'label-default';


 		var statusContent = [];
 		statusContent[3] = '<?php echo $tickets->stateLabels[$tickets->getStatusPlain(3)]; ?>';
 		statusContent[1] = '<?php echo $tickets->stateLabels[$tickets->getStatusPlain(1)]; ?>';
 		statusContent[4] = '<?php echo $tickets->stateLabels[$tickets->getStatusPlain(4)]; ?>';
 		statusContent[2] = '<?php echo $tickets->stateLabels[$tickets->getStatusPlain(2)]; ?>';
 		statusContent[0] = '<?php echo $tickets->stateLabels[$tickets->getStatusPlain(0)]; ?>';
 		statusContent["-1"] = '<?php echo $tickets->stateLabels[$tickets->getStatusPlain(-1)]; ?>';

 		jQuery.ajax({
	        	type: 'POST',
	            url: leantime.appUrl+'/tickets/showAll&raw=true&changeStatus=true',
	            data: 
	            {
		        	id : ticket[1],
		        	status:val
				}
	        });
	       jQuery("#status-"+ticket[1]).attr("class", "f-left "+statusPlain[val]);
	       jQuery("#status-"+ticket[1]).text(statusContent[val]);
	       jQuery('.popoverbtn').popover("hide");
	      colorBoxes();  
 	});
 
	jQuery( "#sortableBacklog" ).disableSelection();
    
    jQuery(".project-select, .user-select, .status-select").chosen();
    
    jQuery( ".sortableTicketList" ).sortable({
        axis: 'y',
        connectWith: ".sortableTicketList",
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
	    update: function (event, ui) {

	        var data = jQuery(this).sortable('serialize');

	        if((data.match(/ticket/g) || []).length>0 && jQuery(this).attr("data-sprint") != ""){
                jQuery('#emptySprint').hide();

            }

            if( jQuery(this).attr("data-sprint") != "")
            {
              jQuery("#countSprintItems").text((data.match(/ticket/g) || []).length);
            }

	        // POST to server using $.post or $.ajax
	        jQuery.ajax({
	            data: data,
	            type: 'POST',
	            url: leantime.appUrl+'/tickets/showAll&raw=true&sort=true&sprint='+jQuery(this).attr("data-sprint")
	        });

	    }
    });
    
    jQuery(".punchIn").on("click", function(){
    	
    	var ticketId = jQuery(this).attr("value");
    	
    	// POST to server using $.post or $.ajax
	        jQuery.ajax({
	            data: "ticketId="+ticketId,
	            type: 'POST',
	            url: leantime.appUrl+'/tickets/showAll&raw=true&punchIn=true'
	        });
	        var currentdate = new Date(); 
	        	        	        
			var datetime = currentdate.getHours() + ":"  
                + currentdate.getMinutes() + " ";
            
            jQuery(".timerContainer .punchIn").hide();     
            jQuery("#timerContainer-"+ticketId+" .working").show();
	        jQuery("#timerContainer-"+ticketId+" span.time").text(datetime);

    });
	
	jQuery(".punchOut").on("click", function(){
	    	
	    	var ticketId = jQuery(this).attr("value");
	    	
	    	// POST to server using $.post or $.ajax
		        jQuery.ajax({
		            data: "ticketId="+ticketId,
		            type: 'POST',
		            url: leantime.appUrl+'/tickets/showAll&raw=true&punchOut=true',
		            
		        });
		        
		        jQuery(".timerContainer .punchIn").show();     
				jQuery("#timerContainer-"+ticketId+" .working").hide();   

	});
    
    colorBoxes();

      <?php if(isset($_SESSION['userdata']['settings']["modals"]["backlog"]) === false || $_SESSION['userdata']['settings']["modals"]["backlog"] == 0){     ?>
      leantime.helperController.showHelperModal("backlog");
      <?php
      //Only show once per session
      $_SESSION['userdata']['settings']["modals"]["backlog"] = 1;
      } ?>
    
  });
  
  </script>
  
 <div class="pageheader">           
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
    	<h1><?php echo $language->lang_echo('ALL_TICKETS'); ?></h1>
	</div>
</div><!--pageheader-->
           
<div class="maincontent">
	<div class="maincontentinner">
		<form action="<?=BASE_URL ?>/tickets/showAll" method="post">
            <input type="hidden" value="1" name="search"/>
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
                                echo $dates; ?> - <a href="<?=BASE_URL ?>/sprints/editSprint/<?=$this->get("currentSprint")?>" class="sprintModal">edit Sprint</a>
                            <?php }else{ ?>
                                <a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Create a new Sprint</a>
                            <?php } ?>
                        </small>
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="pull-right">
                        <div class="btn-group mt-1 mx-auto" role="group">
                            <a href="<?=BASE_URL ?>/tickets/showKanban" class="btn btn-sm btn-secondary "><i class="fas fa-columns"></i> Kanban</a>
                            <a href="<?=BASE_URL ?>/tickets/showAll" class="btn btn-sm btn-secondary active"><i class='iconfa-list'></i> List</a>
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


                            <div class="right" style="float:right;">



                            </div>
                            <small><i class="fa <?php echo $todoTypeIcons[$row['type']]; ?>"></i> <?php echo $row['type']; ?></small>
                            <h3><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>" >#<?php echo $row["id"];?> - <?php echo $row["headline"];?></a></h3>
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


                                <a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#comment" ><span class="iconfa-comments"></span><?php echo $row["commentCount"] ?> Comments</a>
                                &nbsp;&nbsp;<a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#files"><span class="iconfa-paper-clip"></span><?php echo $row["fileCount"] ?> Files</a>
                                <?php


                                if(isset($row["tags"]) && !empty($row["tags"])){ ?>
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
                                */?>
                                <a class="effortPopover" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id']."' value='' data-label='Effort not clear' style='float:left; margin-right:10px;' "; if($row['storypoints'] == '') echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id']."'>Effort not clear</label>";foreach($this->get('efforts') as $effortKey => $effortValue){ echo"<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id'].$effortKey."' value='".$effortKey."' data-label='".$effortValue."' style='float:left; margin-right:10px;' "; if($row['storypoints'] == $effortKey) echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="How big is this ToDo?">
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

                                    <a class="milestonePopover" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                  <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                           <?php echo $row['milestoneHeadline']; ?>
                                                    </span>
                                    </a>

                                <?php }else{ ?>

                                    <a class="milestonePopover" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
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

            <div class="clearfix"></div>



            <div class="row">
                <div class="col-md-4">
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-th"></i> &nbsp; Add <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/tickets/newTicket"><span class="iconfa-pushpin"></span> Add ToDo</a></li>
                            <li><a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal"><span class="fa fa-map"></span> Add Milestone</a></li>
                            <li><a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Add Sprint</a></li>
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

            if((count($searchCriteria['users']) == 0 || $searchCriteria['users'] == '') && $searchCriteria['milestone'] == '' && $searchCriteria['searchType'] == '' && trim($searchCriteria['status']) == 'not_done') { echo "hideOnLoad"; } ?>">

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
                        <small><i class="fa <?php echo $todoTypeIcons[$row['type']]; ?>"></i> <?php echo $row['type']; ?></small>
						<h3><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>" >#<?php echo $row["id"];?> - <?php echo $row["headline"];?></a></h3>
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

                            <a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#comment"><span class="iconfa-comments"></span><?php echo $row["commentCount"] ?> Comments</a>
							<a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#files"><span class="iconfa-paper-clip"></span><?php echo $row["fileCount"] ?> Files</a>
                            <?php if(isset($row["tags"]) && !empty($row["tags"])){ ?>
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

                            <a class="effortPopover" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id']."' value='' data-label='Effort not clear' style='float:left; margin-right:10px;' "; if($row['storypoints'] == '') echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id']."'>Effort not clear</label>";foreach($this->get('efforts') as $effortKey => $effortValue){ echo"<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id'].$effortKey."' value='".$effortKey."' data-label='".$effortValue."' style='float:left; margin-right:10px;' "; if($row['storypoints'] == $effortKey) echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="How big is this ToDo?">
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

                                <a class="milestonePopover" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                  <span id="milestone-<?php echo $row['id'] ?>" class="f-left label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                           <?php echo $row['milestoneHeadline']; ?>
                                                    </span>
                                </a>

                            <?php }else{ ?>

                                <a class="milestonePopover" style="display:block; float:left;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="left" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
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
        </form>
        <?php
        if(isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1){     ?>
            <p class="align-center"><br /><em>Once you added a few items to your sprint. Go to your Kanban Board</em> <br /><a href="<?=BASE_URL ?>/tickets/showKanban/" class="btn btn-primary"><span class="fas fa-columns"></span> Kanban Board</a></p>
        <?php } ?>

	</div>
</div>
        