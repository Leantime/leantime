<?php

    defined( 'RESTRICTED' ) or die( 'Restricted access' );

    $sprints        = $this->get("sprints");
    $searchCriteria = $this->get("searchCriteria");
    $currentSprint  = $this->get("currentSprint");
    $allTickets     = $this->get('allTickets');

    $todoTypeIcons  = $this->get("ticketTypeIcons");

    $efforts        = $this->get('efforts');
    $priorities     = $this->get('priorities');
    $statusLabels   = $this->get('allTicketStates');
    $groupBy        = $this->get('groupBy');
    $newField       = $this->get('newField');

    //All states >0 (<1 is archive)
    $numberofColumns = count($this->get('allTicketStates'))-1;
    $size = floor(100 / $numberofColumns);

?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-fw fa-thumb-tack"></span></div>
    <div class="pagetitle">
       <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
   	    <h1><?php echo $this->__("headlines.todos"); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
	<div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

		<form action="" method="get" id="ticketSearch">

            <?php $this->dispatchTplEvent('filters.afterFormOpen'); ?>

            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-5">
                    <?php
                    $this->dispatchTplEvent('filters.afterLefthandSectionOpen');
                    if ($login::userIsAtLeast($roles::$editor) && !empty($newField)) {
                    ?>
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?=$this->__("links.new_with_icon") ?> <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <?php foreach($newField as $option) { ?>
                                <li>
                                    <a
                                        href="<?= !empty($option['url']) ? $option['url'] : '' ?>"
                                        class="<?= !empty($option['class']) ? $option['class'] : '' ?>"
                                    > <?= !empty($option['text']) ? $this->__($option['text']) : '' ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php
                    }
                    $this->dispatchTplEvent('filters.beforeLefthandSectionClose');
                    ?>
                </div>

                <div class="col-md-2 center">

                    <?php $this->dispatchTplEvent('filters.afterCenterSectionOpen'); ?>
                    <span class="currentSprint">
                        <?php if($this->get('sprints') !== false && count($this->get('sprints'))  > 0) {?>
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_sprint") ?>" title="<?=$this->__("input.placeholders.filter_by_sprint") ?>" name="sprint" class="mainSprintSelector" onchange="form.submit()" id="sprintSelect">
                            <option value="all" <?php if($searchCriteria['sprint'] != "all") echo"selected='selected'"; ?>><?=$this->__("links.all_todos") ?></option>
                            <option value="backlog" <?php if($searchCriteria['sprint'] == "backlog") echo"selected='selected'"; ?>><?=$this->__("links.backlog") ?></option>
                                <?php
                                $dates = "";
                                foreach($this->get('sprints') as $sprintRow){ 	?>

                                    <?php echo"<option value='".$sprintRow->id."'";

                                    if($this->get("currentSprint") !== false && $sprintRow->id == $this->get("currentSprint")) {
                                        echo " selected='selected' ";

                                        $dates = sprintf($this->__("label.date_from_date_to"), $this->getFormattedDateString($sprintRow->startDate), $this->getFormattedDateString($sprintRow->endDate));
                                    }
                                    echo ">";
                                    $this->e($sprintRow->name);
                                    echo "</option>";
                                    ?>

                                <?php } 	?>
                            </select>
                            <br/>
                            <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                                <small>
                                <?php if($dates != "") {
                                    echo $dates; ?> - <a href="<?=BASE_URL ?>/sprints/editSprint/<?=$this->get("currentSprint")?>" class="sprintModal"><?=$this->__("links.edit_sprint") ?></a>
                                <?php }else{ ?>
                                    <a href="<?=BASE_URL ?>/sprints/editSprint" class="sprintModal"><?=$this->__("links.create_sprint") ?></a>
                                <?php } ?>
                                </small>
                            <?php } ?>
                        <?php } ?>
                    </span>
                    <?php $this->dispatchTplEvent('filters.beforeCenterSectionClose'); ?>
                </div>
                <div class="col-md-5">
                    <div class="pull-right">

                        <?php $this->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                        <div id="tableButtons" style="display:inline-block"></div>
                        <a onclick="leantime.ticketsController.toggleFilterBar();" class="btn btn-default"><?=$this->__("links.filter") ?></a>
                        <div class="btn-group viewDropDown">

                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"><?=$this->__("links.group_by") ?></button>
                            <ul class="dropdown-menu">
                                <?php foreach ($groupBy as $input): ?>
                                    <li>
                                        <span class="radio">
                                            <input
                                                type="radio"
                                                name="groupBy"
                                                <?php if($searchCriteria["groupBy"] == $input['status']){echo "checked='checked'";}?>
                                                value="<?php echo $input['status']; ?>"
                                                id="<?php echo $input['id']; ?>"
                                                onclick="jQuery('#ticketSearch').submit();"
                                            />
                                            <label for="<?php echo $input['id'] ?>"><?=$this->__("label.{$input['label']}") ?></label>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                        </div>

                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"><?=$this->__("links.table") ?> <?=$this->__("links.view") ?></button>
                            <ul class="dropdown-menu">
                                <li><a href="<?php if(isset($_SESSION['lastFilterdTicketKanbanView']) && $_SESSION['lastFilterdTicketKanbanView'] != ""){ echo $_SESSION['lastFilterdTicketKanbanView']; }else{ echo BASE_URL."/tickets/showKanban"; } ?>" ><?=$this->__("links.kanban") ?></a></li>
                                <li><a href="<?php if(isset($_SESSION['lastFilterdTicketTableView']) && $_SESSION['lastFilterdTicketTableView'] != ""){ echo $_SESSION['lastFilterdTicketTableView']; }else{ echo BASE_URL."/tickets/showAll"; } ?>" class="active"><?=$this->__("links.table") ?></a></li>
                            </ul>
                        </div>

                        <?php $this->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>

                    </div>
                </div>

            </div>

            <div class="clearfix"></div>

            <?php $this->dispatchTplEvent('filters.beforeBar'); ?>

            <div class="filterBar <?php if(!isset($_GET['search'])) { echo "hideOnLoad"; } ?>">

                <div class="row-fluid">

                    <?php $this->dispatchTplEvent('filters.beforeFirstBarField'); ?>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.user") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_user") ?>"  style="width:130px;" title="<?=$this->__("input.placeholders.filter_by_user") ?>" name="users" multiple="multiple" class="user-select" id="userSelect">
                                <option value=""></option>
                                <?php foreach($this->get('users') as $userRow){ 	?>

                                    <?php echo"<option value='".$userRow["id"]."'";

                                    if($searchCriteria['users'] !== false && $searchCriteria['users'] !== null && array_search($userRow["id"], explode(",", $searchCriteria['users'])) !== false) echo" selected='selected' ";

                                    echo">".sprintf( $this->__('text.full_name'), $this->escape($userRow['firstname']), $this->escape($userRow['lastname']))."</option>"; ?>

                                <?php } 	?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.milestone") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" title="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="milestone" id="milestoneSelect">
                                <option value=""><?=$this->__("label.all_milestones") ?></option>
                                <?php foreach($this->get('milestones') as $milestoneRow){ 	?>

                                    <?php echo"<option value='".$milestoneRow->id."'";

                                    if(isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) echo" selected='selected' ";

                                    echo">".$this->escape($milestoneRow->headline)."</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.todo_type") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_type") ?>" title="<?=$this->__("input.placeholders.filter_by_type") ?>" name="type" id="typeSelect">
                                <option value=""><?=$this->__("label.all_types") ?></option>
                                <?php foreach($this->get('types') as $type){ 	?>

                                    <?php echo"<option value='".$type."'";

                                    if(isset($searchCriteria['type']) && ($searchCriteria['type'] == $type)) echo" selected='selected' ";

                                    echo">$type</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.todo_priority") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_priority") ?>" title="<?=$this->__("input.placeholders.filter_by_priority") ?>" name="type" id="prioritySelect">
                                <option value=""><?=$this->__("label.all_priorities") ?></option>
                                <?php foreach($this->get('priorities') as $priorityKey=>$priorityValue){ 	?>

                                    <?php echo"<option value='".$priorityKey."'";

                                    if(isset($searchCriteria['priority']) && ($searchCriteria['priority'] == $priorityKey)) echo" selected='selected' ";

                                    echo">$priorityValue</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.todo_status") ?></label>
                        <div class="form-group">
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_status")?>" name="searchStatus"  multiple="multiple" class="status-select" id="statusSelect">
                                <option value=""></option>
                                <option value="not_done" <?php if($searchCriteria['status'] !== false && strpos($searchCriteria['status'], 'not_done') !== false) echo" selected='selected' ";?>><?=$this->__("label.not_done")?></option>
                                <?php foreach($statusLabels as $key=>$label){?>

                                    <?php echo"<option value='".$key."'";

                                    if($searchCriteria['status'] !== false && array_search((string) $key, explode(",",$searchCriteria['status'])) !== false) echo" selected='selected' ";
                                    echo">". $this->escape($label["name"])."</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="filterBoxLeft">
                        <label class="inline"><?=$this->__("label.search_term") ?></label><br />
                        <input type="text" class="form-control input-default" id="termInput" name="term" placeholder="<?=$this->__("input.placeholders.search") ?>" value="<?php $this->e($searchCriteria['term']); ?>">
                        <input type="submit" value="<?=$this->__("buttons.search") ?>" name="search" class="form-control btn btn-primary" />
                    </div>


                </div>

            </div>

            <?php $this->dispatchTplEvent('filters.beforeFormClose'); ?>

        </form>

        <?php $this->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

        <table id="allTicketsTable" class="table table-bordered display" style="width:100%">
            <colgroup>
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
            </colgroup>
            <?php $this->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
            <thead>
                <?php $this->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                <tr>
                    <th><?= $this->__("label.title"); ?></th>
                    <th class="status-col"><?= $this->__("label.todo_status"); ?></th>
                    <th class="milestone-col"><?= $this->__("label.milestone"); ?></th>
                    <th><?= $this->__("label.effort"); ?></th>
                    <th><?= $this->__("label.priority"); ?></th>
                    <th class="user-col"><?= $this->__("label.editor"); ?>.</th>
                    <th class="sprint-col"><?= $this->__("label.sprint"); ?></th>
                    <th class="tags-col"><?= $this->__("label.tags"); ?></th>
                    <th class="duedate-col"><?= $this->__("label.due_date"); ?></th>
                    <th class="planned-hours-col"><?= $this->__("label.planned_hours"); ?></th>
                    <th class="remaining-hours-col"><?= $this->__("label.estimated_hours_remaining"); ?></th>
                    <th class="booked-hours-col"><?= $this->__("label.booked_hours"); ?></th>
                </tr>
                <?php $this->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
            </thead>
            <?php $this->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
            <tbody>
                <?php $this->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                <?php foreach($allTickets as $rowNum => $row){?>
                    <tr>
                        <?php $this->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                        <td data-order="<?=$this->e($row['headline']); ?>"><a class='ticketModal' href="<?=BASE_URL ?>/tickets/showTicket/<?=$this->e($row['id']); ?>"><?=$this->e($row['headline']); ?></a></td>
                        <td data-order="<?=$statusLabels[$row['status']]["name"]?>">
                            <div class="dropdown ticketDropdown statusDropdown colorized show">
                                <a class="dropdown-toggle f-left status <?=isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]["class"] : '' ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="text">
                                        <?php
                                        if(isset($statusLabels[$row['status']])){
                                        echo $statusLabels[$row['status']]["name"]; }
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



                        <?php
                        if($row['dependingTicketId'] != "" && $row['dependingTicketId'] != 0){
                            $milestoneHeadline = $this->escape($row['milestoneHeadline']);
                        }else{
                            $milestoneHeadline = $this->__("label.no_milestone");
                        }?>

                        <td data-order="<?=$milestoneHeadline?>">
                            <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                <a style="background-color:<?=$this->escape($row['milestoneColor'])?>" class="dropdown-toggle f-left  label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?=$milestoneHeadline?></span>
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
                        <td data-order="<?=$row['storypoints'] ? $efforts[$row['storypoints']] : $this->__("label.story_points_unkown"); ?>">
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
                        <td data-order="<?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?>">
                            <div class="dropdown ticketDropdown priorityDropdown show">
                                <a class="dropdown-toggle f-left  label-default priority" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?php
                                                                if($row['priority'] != '' && $row['priority'] > 0) {
                                                                    echo $priorities[$row['priority']];
                                                                }else{
                                                                    echo $this->__("label.priority_unkown");
                                                                }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="priorityDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.select_priority")?></li>
                                    <?php foreach($priorities as $priorityKey => $priorityValue){
                                        echo"<li class='dropdown-item'>
                                                                        <a href='javascript:void(0);' data-value='".$row['id']."_".$priorityKey."' id='ticketPriorityChange".$row['id'].$priorityKey."'>".$priorityValue."</a>";
                                        echo"</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <td data-order="<?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?>">
                            <div class="dropdown ticketDropdown userDropdown noBg show ">
                                <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                <?php if($row["editorFirstname"] != ""){
                                                                    echo "<span id='userImage".$row['id']."'><img src='".BASE_URL."/api/users?profileImage=".$row['editorProfileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row['id']."'>".$this->escape($row["editorFirstname"])."</span>";
                                                                }else {
                                                                    echo "<span id='userImage".$row['id']."'><img src='".BASE_URL."/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row['id']."'>".$this->__("dropdown.not_assigned")."</span>";
                                                                }?>
                                                            </span>
                                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                    <?php foreach($this->get('users') as $user){
                                        echo "<li class='dropdown-item'>";
                                        echo "<a href='javascript:void(0);' data-label='".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."' data-value='".$row['id']."_".$user['id']."_".$user['profileId']."' id='userStatusChange".$row['id'].$user['id']."' ><img src='".BASE_URL."/api/users?profileImage=".$user['profileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."</a>";
                                        echo "</li>";
                                    }?>
                                </ul>
                            </div>
                        </td>
                        <?php

                        if($row['sprint'] != "" && $row['sprint'] != 0  && $row['sprint'] != -1){
                            $sprintHeadline = $this->escape($row['sprintName']);
                        }else{
                            $sprintHeadline = $this->__("label.backlog");
                        }?>

                        <td data-order="<?=$sprintHeadline?>">

                            <div class="dropdown ticketDropdown sprintDropdown show">
                                <a class="dropdown-toggle f-left  label-default sprint" href="javascript:void(0);" role="button" id="sprintDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="text"><?=$sprintHeadline?></span>
                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="sprintDropdownMenuLink<?=$row['id']?>">
                                    <li class="nav-header border"><?=$this->__("dropdown.choose_sprint")?></li>
                                    <li class='dropdown-item'><a href='javascript:void(0);' data-label="<?=$this->__("label.backlog")?>" data-value='<?=$row['id']."_0"?>'> <?=$this->__("label.backlog")?> </a></li>
                                    <?php if($this->get('sprints')) {
                                        foreach ($this->get('sprints') as $sprint) {
                                            echo "<li class='dropdown-item'>
                                                    <a href='javascript:void(0);' data-label='" . $this->escape($sprint->name) . "' data-value='" . $row['id'] . "_" . $sprint->id . "' id='ticketSprintChange" . $row['id'] . $sprint->id . "' >" . $this->escape($sprint->name) . "</a>";
                                            echo "</li>";
                                        }
                                    }?>
                                </ul>
                            </div>
                        </td>

                        <td data-order="<?=$row['tags'] ?>">
                            <?php if($row['tags'] != ''){?>
                            <?php  $tagsArray = explode(",", $row['tags']); ?>
                                <div class='tagsinput readonly'>
                                    <?php

                                    foreach($tagsArray as $tag){
                                        echo"<span class='tag'><span>".$tag."</span></span>";
                                    }

                                    ?>
                                </div>
                            <?php } ?>
                        </td>

                        <?php
                        if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                            $date = $this->__("text.anytime");

                        }else {
                            $date = new DateTime($row['dateToFinish']);
                            $date = $date->format($this->__("language.dateformat"));

                        }
                        ?>
                        <td data-order="<?=$date?>" >
                            <input type="text" title="<?php echo $this->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $row['id'];?>" name="date" />
                        </td>
                        <td data-order="<?=$this->e($row['planHours']); ?>">
                            <input type="text" value="<?=$this->e($row['planHours']); ?>" name="planHours" class="small-input" onchange="leantime.ticketsController.updatePlannedHours(this, '<?=$row['id']?>'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                        </td>
                        <td data-order="<?=$this->e($row['hourRemaining']); ?>">
                            <input type="text" value="<?=$this->e($row['hourRemaining']); ?>" name="remainingHours" class="small-input" onchange="leantime.ticketsController.updateRemainingHours(this, '<?=$row['id']?>');" />
                        </td>

                        <td data-order="<?php if($row['bookedHours'] === null || $row['bookedHours'] == "") echo "0"; else echo $row['bookedHours']?>">

                            <?php if($row['bookedHours'] === null || $row['bookedHours'] == "") echo "0"; else echo $row['bookedHours']?>
                        </td>
                        <?php $this->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                    </tr>
                <?php } ?>
                <?php $this->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
            </tbody>
            <?php $this->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
        </table>
        <?php $this->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>
	</div>
</div>

<script type="text/javascript">

    <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>

    leantime.ticketsController.initTicketSearchSubmit("<?=BASE_URL ?>/tickets/showAll");


    leantime.ticketsController.initUserSelectBox();
    leantime.ticketsController.initStatusSelectBox();

    <?php if($login::userIsAtLeast($roles::$editor)) { ?>
        leantime.ticketsController.initUserDropdown();
        leantime.ticketsController.initMilestoneDropdown();
        leantime.ticketsController.initEffortDropdown();
        leantime.ticketsController.initPriorityDropdown();
        leantime.ticketsController.initSprintDropdown();
        leantime.ticketsController.initStatusDropdown();
    <?php }else{ ?>
    leantime.generalController.makeInputReadonly(".maincontentinner");
    <?php } ?>



    leantime.ticketsController.initTicketsTable("<?=$searchCriteria["groupBy"] ?>");

    <?php if(isset($_SESSION['userdata']['settings']["modals"]["backlog"]) === false || $_SESSION['userdata']['settings']["modals"]["backlog"] == 0){     ?>
    leantime.helperController.showHelperModal("backlog");
    <?php
    //Only show once per session
    $_SESSION['userdata']['settings']["modals"]["backlog"] = 1;
    } ?>

    <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
