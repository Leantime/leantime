<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$tickets = $this->get("tickets");
$sprints = $this->get("sprints");
$searchCriteria = $this->get("searchCriteria");

$todoTypeIcons = array('Story' => 'fa-book', 'Task' => 'fa-check-square', 'Bug' => 'fa-bug');

$efforts = $this->get('efforts');

?>

 <script type="text/javascript">

     function countTickets () {
         jQuery("#sortableBacklog .column").each(function(){
             var counting= jQuery(this).find('.moveable').length;
             jQuery(this).find(' .count').text(counting);
         });
     }
  
  jQuery(window).bind("load", function () {
  		jQuery(".loading").fadeOut();
        jQuery(".filterBar .row-fluid").css("opacity", "1");
        var height = jQuery("html").height()-320;
        jQuery(".column .contentInner").css("height", height);
      countTickets();
  });

     jQuery(window).resize(function() {
         var height = jQuery("html").height()-320;
         jQuery(".column .contentInner").css("height", height);
     });
      
  jQuery(function() { 
	
	jQuery('.popoverlink').popover({trigger: 'hover'});
	
	jQuery( "#sortableBacklog" ).disableSelection();
    
    jQuery(".project-select, .user-select, .status-select").chosen();
    
    jQuery(".ticketBox").hover(function(){
    	jQuery(this).css("background", "#f9f9f9");
    },function(){
    	jQuery(this).css("background", "#ffffff");
    });
    
    jQuery(".contentInner").sortable({
    	connectWith: ".contentInner",
        items: "> .moveable",
        tolerance: 'intersect',
        placeholder: "ui-state-highlight",
    	forcePlaceholderSize: true,
	    cancel: ".portlet-toggle,:input,a,input",
        distance: 25,

	    start: function (event, ui) {
	        ui.item.addClass('tilt');
	        tilt_direction(ui.item);
	    },
	    stop: function (event, ui) {
	        ui.item.removeClass("tilt");
	        jQuery("html").unbind('mousemove', ui.item.data("move_handler"));
	        ui.item.removeData("move_handler");
	    },
	    update: function (event, ui) {
            countTickets();
			 // POST to server using $.post or $.ajax
	        jQuery.ajax({
	        	type: 'POST',
	            url: leantime.appUrl+'/tickets/showKanban&raw=true&sort=true',
	            data: 
	            {
		        	<?php foreach($this->get('allTicketStates') as $key => $statusRow){ ?>
					<?php echo $key ?>: jQuery(".contentInner.status_<?php echo $key ?>").sortable('serialize'),
		        	<?php } ?>
		        	statusX: ""
				}
	        });
	        				        
	    }
    });
    
    function tilt_direction(item) {
	    var left_pos = item.position().left,
	        move_handler = function (e) {
	            if (e.pageX >= left_pos) {
	                item.addClass("right");
	                item.removeClass("left");
	            } else {
	                item.addClass("left");
	                item.removeClass("right");
	            }
	            left_pos = e.pageX;
	        };
	    jQuery("html").bind("mousemove", move_handler);
	    item.data("move_handler", move_handler);
	}  
	
	jQuery( ".portlet" )
	    .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
	    .find( ".portlet-header" )
	    .addClass( "ui-widget-header ui-corner-all" )
	    .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
	
	jQuery( ".portlet-toggle" ).click(function() {
	    var icon = jQuery( this );
	    icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
	    icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
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

      <?php if(isset($_SESSION['userdata']['settings']["modals"]["kanban"]) === false || $_SESSION['userdata']['settings']["modals"]["kanban"] == 0){     ?>
      leantime.helperController.showHelperModal("kanban");
      <?php
      //Only show once per session
      $_SESSION['userdata']['settings']["modals"]["kanban"] = 1;
      } ?>
  });
  
  </script>
  <style type="text/css">
  <?php 
	$numberofStatus = count($this->get('allTicketStates'));
	$size = floor(100 / $numberofStatus)
	?>
	.tilt.right {
	    transform: rotate(3deg);
	    -moz-transform: rotate(3deg);
	    -webkit-transform: rotate(3deg);
	}
	.tilt.left {
	    transform: rotate(-3deg);
	    -moz-transform: rotate(-3deg);
	    -webkit-transform: rotate(-3deg);
	}

  	.column {
  		box-sizing: border-box;
  		height:auto;
  		padding:5px;
  		float:left;
  		width:<?php echo $size?>%;
  	}

  .column:first-child {
    padding-left:0px;
  }

  	.column .contentInner {
  		background:#f0f0f0;
  		border:1px solid #ccc;
  		padding:10px 5px;
  		min-height:200px;
  		overflow:auto;
  	}
  	
  	.ticketBox:hover {
  		background:#f9f9f9;
  	}
  	
  	.ui-state-highlight {
  		background:#aaa;
  		border:1px dotted #eee;
  		visibility:visible;
  	}
  	
	.portlet {
	    margin: 0 1em 1em 0;
	    padding: 0.3em;
	}
	.portlet-header {
	    padding: 0.2em 0.3em;
	    margin-bottom: 0.5em;
	    position: relative;
	}
	.portlet-toggle {
	    position: absolute;
	    top: 50%;
	    right: 0;
	    margin-top: -8px;
	}
	.portlet-content {
	    padding: 0.4em;
	}
	.portlet-placeholder {
	    border: 1px dotted black;
	    margin: 0 1em 1em 0;
	    height: 50px;
	}
  </style>
 <div class="pageheader">  
 	
 	<div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
	<div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
	    <h1>Current To-Dos</h1>

	</div>
	
	
	
</div><!--pageheader-->
           
<div class="maincontent">

	<div class="maincontentinner">
        <?php
            echo $this->displayNotification();
        ?>

		<form action="<?=BASE_URL ?>/tickets/showKanban" method="post">

            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-4">
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-th"></i> &nbsp; Add <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/tickets/newTicket"><span class="iconfa-pushpin "></span> Add ToDo</a></li>
                            <li><a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal"><span class="fa fa-map"></span> Add Milestone</a></li>
                            <li><a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Add Sprint</a></li>
                        </ul>
                    </div>
                    <a href="javascript:void(0);" onclick="leantime.ticketsController.toggleFilterBar();" class="formLink btn btn-default"><i class="fas fa-filter"></i> Filters</a>
                </div>

                <div class="col-md-4 center">
                    <span class="currentSprint">
                        <?php  if($this->get('sprints') !== false && count($this->get('sprints'))  > 0) { ?>
                        <select data-placeholder="Filter by Sprint..." name="searchSprints" class="mainSprintSelector" onchange="form.submit()">
                                <option value="none" <?php if($searchCriteria['sprint'] !== false && array_search("none", $searchCriteria['sprint']) !== false) echo"selected='selected'"; ?>>Backlog</option>
                            <?php
                            $dates = "";
                            foreach($this->get('sprints') as $sprintRow){ 	?>

                                <?php echo"<option value='".$sprintRow->id."'";

                                if($this->get("currentSprint") !== false && $sprintRow->id == $this->get("currentSprint")) {
                                    echo " selected='selected' ";
                                    $dates = date("m/d/Y", strtotime($sprintRow->startDate)) ." - " .date("m/d/Y", strtotime($sprintRow->endDate));
                                }
                                echo ">";
                                $this->e($sprintRow->name);
                                echo "</option>";
                                ?>

                            <?php } 	?>
                            </select>
                            <br/>
                        <small>
                            <?php if($dates != "") {
                                echo $dates; ?> - <a href="<?=BASE_URL ?>/sprints/editSprint/<?=$this->get("currentSprint")?>" class="sprintModal">edit Sprint</a>
                            <?php }else{ ?>
                                <a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Create a new Sprint</a>
                            <?php } ?>
                        </small>
                        <?php }else{ ?>
                            <br /><h4> <a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><span class="fa fa-rocket"></span> Create your first Sprint</a></h4>
                        <?php } ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="pull-right">
                        <div class="btn-group mt-1 mx-auto" role="group">
                            <a href="<?=BASE_URL ?>/tickets/showKanban" class="btn btn-sm btn-secondary active"><i class="fas fa-columns"></i> Kanban</a>
                            <a href="<?=BASE_URL ?>/tickets/showAll" class="btn btn-sm btn-secondary"><i class='iconfa-list'></i> List</a>
                        </div>

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

                                    echo">".$this->escape($userRow["firstname"]." ".$userRow["lastname"])."</option>"; ?>

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

                                    echo">".$this->escape($milestoneRow->headline)."</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>

                    </div>

                    <div class="">

                        <label class="inline">To-Do Type</label>
                        <div class="form-group">
                            <select data-placeholder="Filter by Type..." name="searchType" onchange="form.submit()" >
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
		</form>


		<div id="sortableBacklog" class="sortableTicketList">
			
			<div class="row-fluid">
				
				<?php
				
				
				
				foreach($this->get('allTicketStates') as $key => $statusRow){ 	
					
					$color = "";
					switch($key){
						case "3": $color = "#b94a48"; break;
						case "1": $color = "#f89406"; break;
						case "4": $color = "#f89406"; break;
						case "2": $color = "#f89406"; break;
						case "0": $color = "#468847"; break;
						case "-1": $color = "#999999"; break;
					}
					?>
						
						<div class="column">
							
                            <h4 class="widgettitle title-primary" style="border-bottom:5px solid <?php echo $color; ?>">
                                <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=ticketlabels&label=<?=$statusRow?>" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                <?php } ?>
                                <strong class="count">0</strong>
                                <?php echo $tickets->stateLabels[$statusRow]; ?></h4>
							<div class="contentInner <?php echo"status_".$key;?>">
                                <div>
                                    <a href="javascript:void(0);" class="quickAddLink" id="ticket_new_link_<?=$key?>"  onclick="jQuery('#ticket_new_<?=$key?>').toggle('fast'); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> Add To-Do</a>
                                    <div class="ticketBox hideOnLoad " id="ticket_new_<?=$key?>">

                                        <form method="post">
                                            <textarea name="headline"></textarea><br />

                                            <input type="hidden" name="milestone" value="<?php echo $searchCriteria['milestone']; ?>" />
                                            <input type="hidden" name="status" value="<?php echo $key; ?>" />
                                            <input type="hidden" name="sprint" value="<?php echo $this->get("currentSprint"); ?> " />
                                            <input type="submit" value="Save" name="quickadd">
                                            <a href="javascript:void(0);" onclick="jQuery('#ticket_new_<?=$key?>').toggle('fast'); jQuery('#ticket_new_link_<?=$key?>').toggle('fast');">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </form>

                                        <div class="clearfix"></div>
                                    </div>
                                </div>
								<?php foreach($this->get('allTickets') as $row) { ?>
									<?php if($row["status"] == $key){?>
									<div class="ticketBox moveable container" id="ticket_<?php echo$row["id"];?>">

                                        <div class="row">

                                            <div class="col-md-12">
                                                <?php

                                                if ($_SESSION['userdata']['role'] !== 'user') {
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

                                                <h4><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>"><?php echo $row["headline"];?></a></h4>
                                                <p class="description"><?php echo substr(strip_tags($row["description"]), 0, 100);?><?php if(strlen($row["description"]) > 0) echo" (...)";?></p>


                                            </div>


                                        </div>

                                        <div class="row">

                                            <div class="col-md-6" style="white-space: nowrap;">
                                                <?php

                                                if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                                                    $date = "Anytime";

                                                }else {
                                                    $date = new DateTime($row['dateToFinish']);
                                                    $date = $date->format("m/d/Y");
                                                }
                                                ?>

                                                Due: <input type="text" value="<?php echo $date ?>" class="quickDueDates secretInput" data-id="<?php echo $row['id'];?>" name="quickDueDate" />

                                            </div>

                                            <div class="col-md-6" style="padding-top:3px;">
                                                <a class="userPopover" style="white-space: nowrap;" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketUserChange_".$row['id']."' id='ticketUserChange".$row['id']."' value='' data-label='nobody' style='float:left; margin-right:10px;' "; if($row['editorId'] == '') echo" checked='selected' "; echo"><label for='ticketUserChange".$row['id']."'>Not assigned</label>";foreach($this->get('users') as $user){ echo"<input type='radio' name='ticketUserChange_".$row['id']."' id='ticketUserChange".$row['id'].$user['id']."' value='".$user['id']."' data-label='".$user['firstname'].", ".$user['lastname']."' style='float:left; margin-right:10px;' "; if($row['editorId'] == $user['id']) echo" checked='selected' "; echo"><label for='ticketUserChange".$row['id'].$user['id']."'>".$user['firstname'].", ".$user['lastname']."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Who is working on this?">
                                                    <span class="author"><span class="iconfa-user"></span>
                                                        <?php if($row["editorFirstname"] != ""){
                                                            echo "<span id='user".$row['id']."'> ". $this->escape($row["editorFirstname"]). "</span> <i class=\"fas fa-caret-down\"></i>";
                                                        }else {
                                                            echo "<span id='user".$row['id']."'>nobody</span> <i class=\"fas fa-caret-down\"></i>";
                                                        }?>
                                                    </span>
                                                </a>
                                            </div>


                                        </div>





										
										<div class="clearfix" ></div>

                                        <div style="float:left;">
                                            </div>
                                        <div  style="float:right; padding-top:5px;">



                                        </div>

                                        <div class="clearfix" style="padding-bottom: 8px;"></div>

										<div class="left" style="float:left;">

											&nbsp;<a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#comment"><span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?></a>
											&nbsp;<a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row["id"];?>#files"><span class="iconfa-paper-clip"></span> <?php echo $row["fileCount"] ?></a>
											&nbsp;<?php
											$tagArray = explode(",", $row["tags"]);

											$tagClass = "";

											if($tagArray !== false && $tagArray[0] != ""){
												$tagCount = count($tagArray);
												$tagClass = "popoverlink";
											}else{
												$tagCount = 0;

											}

											?><a data-content="<?php echo str_replace(",", ", ", $row["tags"]) ?>" class="<?php echo $tagClass; ?>" data-placement="top" data-rel="popover" href="#" data-original-title="Tags" rel="popover"><i class="iconfa-tags"></i> <?php echo $tagCount; ?></a>
										</div>
										
										<div class="right timerContainer" style="float:right" id="timerContainer-<?php echo $row["id"]; ?>" >

                                                <a class="effortPopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id']."' value='' data-label='Effort not clear' style='float:left; margin-right:10px;' "; if($row['storypoints'] == '') echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id']."'>Effort not clear</label>";foreach($this->get('efforts') as $effortKey => $effortValue){ echo"<input type='radio' name='ticketEffortChange_".$row['id']."' id='ticketEffortChange".$row['id'].$effortKey."' value='".$effortKey."' data-label='".$effortValue."' style='float:left; margin-right:10px;' "; if($row['storypoints'] == $effortKey) echo" checked='selected' "; echo"><label for='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="How big is this To-Do?">
                                                  <span id="effort-<?php echo $row['id'] ?>" class="f-left label label-default effort" >
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
                                                  <span id="milestone-<?php echo $row['id'] ?>" class="f-left label label-primary sprint" style="background-color:<?=$row['milestoneColor'] ?>">
                                                           <?php echo $row['milestoneHeadline']; ?>
                                                    </span>
                                                </a>

                                            <?php }else{ ?>

                                                <a class="milestonePopover" href="javascript:void(0);" data-html="true" data-content="<?php echo "<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id']."0' value='0' data-label='No Milestone' data-color='' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == 0) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id']."0'>No Milestone</label>"; foreach($this->get('milestones') as $milestone){ echo"<input type='radio' name='ticketMilestoneChange_".$row['id']."' id='ticketMilestoneChange".$row['id'].$milestone->id."' value='".$milestone->id."' data-label='".$milestone->headline."' data-color='".$milestone->tags."' style='float:left; margin-right:10px;' "; if($row['dependingTicketId'] == $milestone->id) echo" checked='selected' "; echo"><label for='ticketMilestoneChange".$row['id'].$milestone->id."'>".$milestone->headline."</label>"; ?> <?php } 	?>" data-placement="bottom" data-toggle="popover" data-container="body" data-original-title="" title="Choose a milestone">
                                                <span id="milestone-<?php echo $row['id'] ?>" class="f-left label label-primary sprint">
                                                           No Milestone
                                                    </span>
                                                </a>

                                            <?php } ?>

												
										</div>
										<div class="clearfix"></div>
									</div>
									<?php } ?>
								<?php } ?>

							</div>

						</div>
				<?php } ?>
				<div class="clearfix"></div>
			</div>

		</div>
    </div>

</div>





