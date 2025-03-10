<?php

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$sprints = $tpl->get('sprints');
$searchCriteria = $tpl->get('searchCriteria');
$currentSprint = $tpl->get('currentSprint');

$todoTypeIcons = $tpl->get('ticketTypeIcons');

$efforts = $tpl->get('efforts');
$statusLabels = $tpl->get('allTicketStates');
$allTickets = $tpl->get('allTickets');

// All states >0 (<1 is archive)
$numberofColumns = count($tpl->get('allTicketStates')) - 1;
$size = floor(100 / $numberofColumns);

?>

    <?php $tpl->displaySubmodule('tickets-portfolioHeader') ?>

    <div class="maincontent">
        <?php $tpl->displaySubmodule('tickets-portfolioTabs') ?>

        <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <form action="" method="get" id="ticketSearch">

            <?php $tpl->dispatchTplEvent('filters.afterFormOpen'); ?>

            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-5">
                    <?php
                    $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
$tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
?>
                </div>

                <div class="col-md-2 center">
                <?php
                $tpl->dispatchTplEvent('filters.afterCenterSectionOpen');
$tpl->dispatchTplEvent('filters.beforeCenterSectionClose');
?>
                </div>
                <div class="col-md-5">
                    <div class="pull-right">

                        <?php $tpl->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                        <div id="tableButtons" style="display:inline-block"></div>

                        <?php $tpl->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>

                    </div>
                </div>

            </div>

            <?php $tpl->dispatchTplEvent('filters.beforeFormClose'); ?>

            <div class="clearfix"></div>

        </form>

        <?php $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

            <table id="allTicketsTable" class="table table-bordered display" style="width:100%">
                <colgroup>
                    <col class="con1" >
                    <col class="con0">
                    <col class="con1">
                    <col class="con0" >
                    <col class="con1">
                    <col class="con0">
                    <col class="con1" >
                    <col class="con0" >
                    <col class="con1" >
                    <col class="con0" >
                    <col class="con1" >
                    <col class="con0" >


                </colgroup>
                <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
                <thead>
                <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                <tr>
                    <th><?= $tpl->__('label.project_name'); ?></th>
                    <th><?= $tpl->__('label.title'); ?></th>

                    <th class="milestone-col"><?= $tpl->__('label.dependent_on'); ?></th>

                    <th><?= $tpl->__('label.todo_status'); ?></th>

                    <th class="user-col"><?= $tpl->__('label.owner'); ?></th>
                    <th><?= $tpl->__('label.planned_start_date'); ?></th>
                    <th><?= $tpl->__('label.planned_end_date'); ?></th>
                    <th><?= $tpl->__('label.planned_hours'); ?></th>
                    <th><?= $tpl->__('label.estimated_hours_remaining'); ?></th>
                    <th><?= $tpl->__('label.booked_hours'); ?></th>
                    <th><?= $tpl->__('label.progress'); ?></th>
                    <th class="no-sort"></th>

                </tr>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
                </thead>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
                <tbody>
                    <?php $tpl->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                    <?php foreach ($allTickets as $rowNum => $row) {?>
                        <tr>
                            <td><h4><?= $row->projectName; ?> </h4></td>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                            <td data-order="<?= $tpl->e($row->headline); ?>"><a href="#/tickets/editMilestone/<?= $tpl->e($row->id); ?>"><?= $tpl->e($row->headline); ?></a></td>
                            <?php
            if ($row->milestoneid != '' && $row->milestoneid != 0) {
                $milestoneHeadline = $tpl->escape($row->milestoneHeadline);
            } else {
                $milestoneHeadline = $tpl->__('label.no_milestone');
            }?>

                            <td class="dropdown-cell" data-order="<?= $milestoneHeadline?>">
                                <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                    <a style="background-color:<?= $tpl->escape($row->milestoneColor)?>" class="dropdown-toggle label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?= $row->id?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text"><?= $milestoneHeadline?></span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?= $row->id?>">
                                        <li class="nav-header border"><?= $tpl->__('dropdown.choose_milestone')?></li>
                                        <li class='dropdown-item'><a style='background-color:#b0b0b0' href='javascript:void(0);' data-label="<?= $tpl->__('label.no_milestone')?>" data-value='<?= $row->id.'_0_#b0b0b0'?>'> <?= $tpl->__('label.no_milestone')?> </a></li>

                                        <?php foreach ($tpl->get('milestones') as $milestone) {
                                            if ($milestone->id != $row->id) {
                                                echo "<li class='dropdown-item'>
                                                    <a href='javascript:void(0);' data-label='".$tpl->escape($milestone->headline)."' data-value='".$row->id.'_'.$milestone->id.'_'.$tpl->escape($milestone->tags)."' id='ticketMilestoneChange".$row->id.$milestone->id."' style='background-color:".$tpl->escape($milestone->tags)."'>".$tpl->escape($milestone->headline).'</a>';
                                                echo '</li>';
                                            }
                                        }?>
                                    </ul>
                                </div>
                            </td>

                            <?php

                            if (isset($statusLabels[$row->status])) {
                                $class = $statusLabels[$row->status]['class'];
                                $name = $statusLabels[$row->status]['name'];
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                            }

                        ?>
                            <td class="dropdown-cell" data-order="<?= $name?>">
                                <div class="dropdown ticketDropdown statusDropdown colorized show">
                                    <a class="dropdown-toggle status <?= $class?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?= $row->id?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">
                                            <?php echo $name; ?>
                                        </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?= $row->id?>">
                                        <li class="nav-header border"><?= $tpl->__('dropdown.choose_status')?></li>
                                        <?php foreach ($statusLabels as $key => $label) {
                                            echo "<li class='dropdown-item'>
                                                <a href='javascript:void(0);' class='".$label['class']."' data-label='".$tpl->escape($label['name'])."' data-value='".$row->id.'_'.$key.'_'.$label['class']."' id='ticketStatusChange".$row->id.$key."' >".$tpl->escape($label['name']).'</a>';
                                            echo '</li>';
                                        }?>
                                    </ul>
                                </div>
                            </td>

                            <td class="dropdown-cell" data-order="<?= $row->editorFirstname != '' ? $tpl->escape($row->editorFirstname) : $tpl->__('dropdown.not_assigned')?>">
                                <div class="dropdown ticketDropdown userDropdown noBg show ">
                                    <a class="dropdown-toggle" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?= $row->id?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text">
                                                                    <?php if ($row->editorFirstname != '') {
                                                                        echo "<span id='userImage".$row->id."'><img src='".BASE_URL.'/api/users?profileImage='.$row->editorId."' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row->id."'> ".$tpl->escape($row->editorFirstname).'</span>';
                                                                    } else {
                                                                        echo "<span id='userImage".$row->id."'><img src='".BASE_URL."/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user".$row->id."'>".$tpl->__('dropdown.not_assigned').'</span>';
                                                                    }?>
                                                                </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?= $row->id?>">
                                        <li class="nav-header border"><?= $tpl->__('dropdown.choose_user')?></li>

                                        <?php foreach ($tpl->get('users') as $user) {
                                            echo "<li class='dropdown-item'>
                                                                <a href='javascript:void(0);' data-label='".sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname']))."' data-value='".$row->id.'_'.$user['id'].'_'.$user['profileId']."' id='userStatusChange".$row->id.$user['id']."' ><img src='".BASE_URL.'/api/users?profileImage='.$user['id']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])).'</a>';
                                            echo '</li>';
                                        }?>
                                    </ul>
                                </div>
                            </td>

                            <td data-order="<?php echo $row->editFrom ?>" >
                                <?php echo $tpl->__('label.due_icon'); ?><input type="text" title="<?php echo $tpl->__('label.planned_start_date'); ?>" value="<?php echo format($row->editFrom)->date() ?>" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-<?php echo $row->id; ?>" data-id="<?php echo $row->id; ?>" name="editFrom" class=""/>
                            </td>

                            <td data-order="<?php echo $row->editTo ?>" >
                                <?php echo $tpl->__('label.due_icon'); ?><input type="text" title="<?php echo $tpl->__('label.planned_end_date'); ?>" value="<?php echo format($row->editTo)->date() ?>" class="editToDate secretInput milestoneEditToAsync toDateTicket-<?php echo $row->id; ?>" data-id="<?php echo $row->id; ?>" name="editTo" class="" />

                            </td>

                            <td data-order="<?= $row->planHours?>" >
                                <?php echo $row->planHours ?>
                            </td>
                            <td data-order="<?= $row->hourRemaining?>" >
                                <?php echo $row->hourRemaining ?>
                            </td>
                            <td data-order="<?= $row->bookedHours?>" >
                                <?php echo $row->bookedHours ?>
                            </td>

                            <td data-order="<?= $row->percentDone?>">

                                <div class="progress " style="width: 100%;">

                                    <div class="progress-bar progress-bar-success " role="progressbar" aria-valuenow="<?php echo $row->percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row->percentDone; ?>%">
                                        <span class="sr-only"><?= sprintf($tpl->__('text.percent_complete'), $row->percentDone)?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <div class="inlineDropDownContainer">

                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="nav-header"><?php echo $tpl->__('subtitles.todo'); ?></li>
                                            <li><a href="#/tickets/editMilestone/<?php echo $row->id; ?>" class='ticketModal'><i class="fa fa-edit"></i> <?php echo $tpl->__('links.edit_milestone'); ?></a></li>
                                            <li><a href="#/tickets/moveTicket/<?php echo $row->id; ?>" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $tpl->__('links.move_milestone'); ?></a></li>
                                            <li><a href="#/tickets/delMilestone/<?php echo $row->id; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete'); ?></a></li>
                                            <li class="nav-header border"></li>
                                            <li><a href="<?= BASE_URL?>/tickets/showAll?search=true&milestone=<?= $row->id?>"><?= $tpl->__('links.view_todos')?></a></li>
                                        </ul>
                                    </div>
                                <?php } ?>


                            </td>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                        </tr>
                    <?php } ?>
                    <?php $tpl->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
                </tbody>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
            </table>
            <?php $tpl->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>

    </div>
</div>

<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function(){
    });

    leantime.ticketsController.initTicketSearchSubmit("<?= BASE_URL ?>/tickets/showAll");

    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
    leantime.ticketsController.initUserDropdown();
    leantime.ticketsController.initMilestoneDropdown();
    leantime.ticketsController.initEffortDropdown();
    leantime.ticketsController.initStatusDropdown();
    leantime.ticketsController.initSprintDropdown();
    leantime.ticketsController.initMilestoneDatesAsyncUpdate();

    <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");
    <?php } ?>

    leantime.ticketsController.initMilestoneTable("<?= $searchCriteria['groupBy'] ?>");

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
