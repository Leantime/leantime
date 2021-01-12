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
    <div class="pageicon"><span class="fa fa-sliders"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1><?=$this->__("headline.milestones"); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
	<div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

		<form action="" method="get" id="ticketSearch">
            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-5">
                    <a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal btn btn-primary"><?=$this->__("links.add_milestone"); ?></a>
                </div>

                <div class="col-md-2 center">

                </div>
                <div class="col-md-5">
                    <div class="pull-right">

                        <div id="tableButtons" style="display:inline-block"></div>


                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("links.table") ?> <?=$this->__("links.view") ?></button>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/tickets/roadmap" ><?=$this->__("links.gantt_view") ?></a></li>
                                <li><a href="<?=BASE_URL ?>/tickets/showAllMilestones" class="active"><?=$this->__("links.table") ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

            <div class="clearfix"></div>

        </form>
            <table id="allTicketsTable" class="table table-bordered display" style="width:100%">
                <colgroup>
                    <col class="con1" width="15%">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0" width="120px">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1" width="50px">
                    <col class="con0" width="50px">
                    <col class="con1" width="50px">
                    <col class="con0" width="15%">


                </colgroup>
                <thead>
                <tr>
                    <th><?= $this->__("label.title"); ?></th>

                    <th class="milestone-col"><?= $this->__("label.dependent_on"); ?></th>

                    <th><?= $this->__("label.todo_status"); ?></th>

                    <th class="user-col"><?=$this->__("label.owner"); ?></th>
                    <th><?= $this->__("label.planned_start_date"); ?></th>
                    <th><?= $this->__("label.planned_end_date"); ?></th>
                    <th><?= $this->__("label.planned_hours"); ?></th>
                    <th><?= $this->__("label.estimated_hours_remaining"); ?></th>
                    <th><?= $this->__("label.booked_hours"); ?></th>
                    <th><?= $this->__("label.progress"); ?></th>

                </tr>
                </thead>
                <tbody>
                    <?php foreach($this->get('allTickets') as $row){?>
                        <tr>
                            <td data-order="<?=$this->e($row->headline); ?>"><a href="<?=BASE_URL ?>/tickets/editMilestone/<?=$this->e($row->id); ?>" class="milestoneModal"><?=$this->e($row->headline); ?></a></td>
                            <?php
                            if($row->dependingTicketId != "" && $row->dependingTicketId != 0){
                                $milestoneHeadline = $this->escape($row->milestoneHeadline);
                            }else{
                                $milestoneHeadline = $this->__("label.no_milestone");
                            }?>

                            <td data-order="<?=$milestoneHeadline?>">
                                <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                    <a style="background-color:<?=$this->escape($row->milestoneColor)?>" class="dropdown-toggle f-left  label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row->id?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text"><?=$milestoneHeadline?></span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?=$row->id?>">
                                        <li class="nav-header border"><?=$this->__("dropdown.choose_milestone")?></li>
                                        <li class='dropdown-item'><a style='background-color:#1b75bb' href='javascript:void(0);' data-label="<?=$this->__("label.no_milestone")?>" data-value='<?=$row->id."_0_#1b75bb"?>'> <?=$this->__("label.no_milestone")?> </a></li>

                                        <?php foreach($this->get('milestones') as $milestone){
                                            if($milestone->id != $row->id) {
                                                echo "<li class='dropdown-item'>
                                                <a href='javascript:void(0);' data-label='" . $this->escape($milestone->headline) . "' data-value='" . $row->id . "_" . $milestone->id . "_" . $this->escape($milestone->tags) . "' id='ticketMilestoneChange" . $row->id . $milestone->id . "' style='background-color:" . $this->escape($milestone->tags) . "'>" . $this->escape($milestone->headline) . "</a>";
                                                echo "</li>";
                                            }
                                        }?>
                                    </ul>
                                </div>
                            </td>

                            <td data-order="<?=$statusLabels[$row->status]["name"]?>">
                                <div class="dropdown ticketDropdown statusDropdown colorized show">
                                    <a class="dropdown-toggle f-left status <?=$statusLabels[$row->status]["class"]?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row->id?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">
                                            <?php echo $statusLabels[$row->status]["name"]; ?>
                                        </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row->id?>">
                                        <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>
                                        <?php foreach($statusLabels as $key=>$label){
                                            echo"<li class='dropdown-item'>
                                                <a href='javascript:void(0);' class='".$label["class"]."' data-label='".$this->escape($label["name"])."' data-value='".$row->id."_".$key."_".$label["class"]."' id='ticketStatusChange".$row->id.$key."' >".$this->escape($label["name"])."</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>
                            </td>

                            <td data-order="<?=$row->editorFirstname != "" ?  $this->escape($row->editorFirstname) : $this->__("dropdown.not_assigned")?>">
                                <div class="dropdown ticketDropdown userDropdown noBg show ">
                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row->id?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text">
                                                                    <?php if($row->editorFirstname != ""){
                                                                        echo "<span id='userImage".$row->id."'><img src='".BASE_URL."/api/users?profileImage=".$row->editorProfileId."' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row->id."'> ". $this->escape($row->editorFirstname). "</span>";
                                                                    }else {
                                                                        echo "<span id='userImage".$row->id."'><img src='".BASE_URL."/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row->id."'>".$this->__("dropdown.not_assigned")."</span>";
                                                                    }?>
                                                                </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row->id?>">
                                        <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                        <?php foreach($this->get('users') as $user){
                                            echo"<li class='dropdown-item'>
                                                                <a href='javascript:void(0);' data-label='".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."' data-value='".$row->id."_".$user['id']."_".$user['profileId']."' id='userStatusChange".$row->id.$user['id']."' ><img src='".BASE_URL."/api/users?profileImage=".$user['profileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>
                            </td>

                            <td data-order="<?php echo $this->getFormattedDateString($row->editFrom) ?>" >
                                <?php echo $this->__("label.due_icon"); ?><input type="text" title="<?php echo $this->__("label.planned_start_date"); ?>" value="<?php echo $this->getFormattedDateString($row->editFrom) ?>" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-<?php echo $row->id;?>" data-id="<?php echo $row->id;?>" name="editFrom" class=""/>
                            </td>

                            <td data-order="<?php echo $this->getFormattedDateString($row->editTo) ?>" >
                                <?php echo $this->__("label.due_icon"); ?><input type="text" title="<?php echo $this->__("label.planned_end_date"); ?>" value="<?php echo $this->getFormattedDateString($row->editTo) ?>" class="editToDate secretInput milestoneEditToAsync toDateTicket-<?php echo $row->id;?>" data-id="<?php echo $row->id;?>" name="editTo" class="" />

                            </td>

                            <td data-order="<?=$row->planHours?>" >
                                <?php echo $row->planHours ?>
                            </td>
                            <td data-order="<?=$row->hourRemaining?>" >
                                <?php echo $row->hourRemaining ?>
                            </td>
                            <td data-order="<?=$row->bookedHours?>" >
                                <?php echo $row->bookedHours ?>
                            </td>

                            <td data-order="<?=$row->percentDone?>">
                                <?=sprintf($this->__("text.percent_complete"), $row->percentDone)?>
                                <a style="float:right;" href="<?=BASE_URL?>/tickets/showAll?search=true&milestone=<?=$row->id?>"><?=$this->__("links.view_todos")?></a>

                                <div class="progress" style="width: 100%;">

                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row->percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row->percentDone; ?>%">
                                        <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row->percentDone)?></span>
                                    </div>
                                </div>
                            </td>

                        </tr>

                    <?php } ?>
                </tbody>

            </table>
	</div>
</div>

<script type="text/javascript">

    leantime.ticketsController.initTicketSearchSubmit("<?=BASE_URL ?>/tickets/showAll");

    leantime.ticketsController.initUserDropdown();
    leantime.ticketsController.initMilestoneDropdown();
    leantime.ticketsController.initEffortDropdown();
    leantime.ticketsController.initStatusDropdown();
    leantime.ticketsController.initSprintDropdown();
    leantime.ticketsController.initUserSelectBox();
    leantime.ticketsController.initStatusSelectBox();

    leantime.ticketsController.initMilestoneDatesAsyncUpdate();

    leantime.ticketsController.initTicketsTable("<?=$searchCriteria["groupBy"] ?>");

</script>
