<?php

defined('RESTRICTED') or exit('Restricted access');

echo $tpl->displayNotification();
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$tickets = $tpl->get('tickets');
$sprints = $tpl->get('sprints');
$searchCriteria = $tpl->get('searchCriteria');
$currentSprint = $tpl->get('currentSprint');

$todoTypeIcons = $tpl->get('ticketTypeIcons');

$efforts = $tpl->get('efforts');
$priorities = $tpl->get('priorities');

$allTicketGroups = $tpl->get('allTickets');

// Get quick-add reopen state from session
$reopenState = session()->get('quickadd_reopen', null);

// Get current groupBy for JavaScript access
$currentGroupBy = $searchCriteria['groupBy'] ?? 'all';

?>

<script>
    // Expose current groupBy setting to JavaScript
    leantime.kanbanGroupBy = '<?= $tpl->escape($currentGroupBy) ?>';
</script>

<?php $tpl->displaySubmodule('tickets-ticketHeader') ?>

<div class="maincontent">

    <?php $tpl->displaySubmodule('tickets-ticketBoardTabs') ?>

    <div class="maincontentinner kanban-board-wrapper" >

         <div class="row">
            <div class="col-md-4">
                <?php
                $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');

$tpl->displaySubmodule('tickets-ticketNewBtn');
$tpl->displaySubmodule('tickets-ticketFilter');

$tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">

            </div>
        </div>

        <div class="clearfix"></div>


        <?php if (isset($allTicketGroups['all'])) {
            $allTickets = $allTicketGroups['all']['items'];
        }
?>
        <?php
        // Detect if groupBy is active (not "all")
        $isGroupByActive = ! empty($searchCriteria['groupBy']) && $searchCriteria['groupBy'] !== 'all';
$columnHeaderClass = $isGroupByActive ? 'groupby-active' : '';
?>
        <div class="kanban-column-headers <?= $columnHeaderClass ?>" style="
            display: flex;
            position: sticky;
            top: 110px;
            justify-content: flex-start;
            z-index: 9;
            ">
        <?php foreach ($tpl->get('allKanbanColumns') as $key => $statusRow) { ?>
            <div class="column">

                <h4 class="widgettitle title-primary title-border-<?php echo $statusRow['class']; ?>">

                    <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                        <div class="inlineDropDownContainer" style="float:right;">
                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>

                            <ul class="dropdown-menu">
                                <li><a href="#/setting/editBoxLabel?module=ticketlabels&label=<?= $key?>" class="editLabelModal"><?= $tpl->__('headlines.edit_label')?></a>
                                </li>
                                <li><a href="<?= BASE_URL ?>/projects/showProject/<?= session('currentProject'); ?>#todosettings"><?= $tpl->__('links.add_remove_col')?></a></li>
                            </ul>
                        </div>
                    <?php } ?>

                    <strong class="count">0</strong>
                    <?php $tpl->e($statusRow['name']); ?>

                </h4>

            </div>
        <?php } ?>
        </div>

        <?php foreach ($allTicketGroups as $group) {?>
             <?php
$allTickets = $group['items'];
            ?>

            <?php if ($group['label'] != 'all') { ?>
                <!-- Swimlane row wrapper - PRD v2 compliant 150px horizontal layout -->
                <?php
                $swimlaneExpanded = ! in_array($group['id'], session('collapsedSwimlanes', []));
                $groupBy = $searchCriteria['groupBy'] ?? 'status';

                // Get pre-calculated status breakdown with timeAlert from service
                // Use string key to match how PHP array keys work with mixed types
                $statusBreakdown = $tpl->get('statusBreakdown');
                $groupIdKey = (string) $group['id'];
                $swimlaneBreakdown = $statusBreakdown[$groupIdKey] ?? $statusBreakdown[$group['id']] ?? [];
                $statusCounts = $swimlaneBreakdown['statusCounts'] ?? [];
                $timeAlert = $swimlaneBreakdown['timeAlert'] ?? null;
                ?>
                <div class="kanban-swimlane-row" data-expanded="<?= $swimlaneExpanded ? 'true' : 'false' ?>" id="swimlane-row-<?= $group['id'] ?>">
                    <!-- Sentinel for Intersection Observer sticky detection -->
                    <div class="kanban-swimlane-sentinel" data-swimlane-id="<?= $group['id'] ?>" aria-hidden="true"></div>

                    <?php
                    // Render the blade component for the header
                    echo app('blade.compiler')::render(
                        '<x-global::kanban.swimlane-row-header
                            :groupBy="$groupBy"
                            :groupId="$groupId"
                            :label="$label"
                            :totalCount="$totalCount"
                            :statusCounts="$statusCounts"
                            :statusColumns="$statusColumns"
                            :expanded="$expanded"
                            :moreInfo="$moreInfo"
                            :timeAlert="$timeAlert"
                        />',
                        [
                            'groupBy' => $groupBy,
                            'groupId' => $group['id'],
                            'label' => $group['label'],
                            'totalCount' => $swimlaneBreakdown['totalCount'] ?? count($group['items']),
                            'statusCounts' => $statusCounts,
                            'statusColumns' => $tpl->get('allKanbanColumns'),
                            'expanded' => $swimlaneExpanded,
                            'moreInfo' => $group['more-info'] ?? null,
                            'timeAlert' => $group['timeAlert'] ?? null,
                        ]
                    );
                ?>

                    <!-- Kanban columns content area (toggles between expanded and collapsed/compact) -->
                    <div class="kanban-swimlane-content<?= ! $swimlaneExpanded ? ' collapsed' : '' ?>" id="swimlane-content-<?= $group['id'] ?>">
            <?php } ?>

                    <div class="sortableTicketList kanbanBoard" id="kanboard-<?= $group['id'] ?>" style="margin-top:-5px;">

                        <div class="row-fluid">

                            <?php
                /**
                 * Detect empty columns for visual indicator
                 * Loop through all status columns and check if any tickets exist for that status
                 */
                $emptyColumns = [];
            foreach ($tpl->get('allKanbanColumns') as $key => $statusRow) {
                $hasTickets = false;

                // Check if current group has any tickets in this status
                if (isset($allTickets)) {
                    foreach ($allTickets as $ticket) {
                        if (isset($ticket['status']) && $ticket['status'] == $key) {
                            $hasTickets = true;
                            break;
                        }
                    }
                }

                if (! $hasTickets) {
                    $emptyColumns[$key] = true;
                }
            }
            ?>

                            <?php foreach ($tpl->get('allKanbanColumns') as $key => $statusRow) { ?>
                            <div class="column">
                                <div class="contentInner <?php echo 'status_'.$key; ?> <?= isset($emptyColumns[$key]) ? 'empty-column' : '' ?>"
                                     data-empty-text="<?= isset($emptyColumns[$key]) ? 'Empty' : '' ?>"
                                     aria-label="<?= isset($emptyColumns[$key]) ? 'Empty column' : htmlspecialchars($statusRow['name']).' column items' ?>"
                                     role="list">

                                    <?php
                                        $statusId = $key;
                                $swimlaneKey = $group['id'] ?? null;
                                $isEmpty = isset($emptyColumns[$key]);
                                $currentGroupBy = $searchCriteria['groupBy'] ?? null;
                                include __DIR__.'/partials/quickadd-form.inc.php';
                                ?>

                                    <?php foreach ($allTickets as $row) { ?>
                                        <?php if ($row['status'] == $key) {?>
                                        <div class="ticketBox moveable container priority-border-<?= $row['priority']?>" id="ticket_<?php echo $row['id']; ?>">

                                            <div class="row" >

                                                <div class="col-md-12">


                                                    <?php echo app('blade.compiler')::render('@include("tickets::partials.ticketsubmenu", [
                                                                                        "ticket" => $ticket,
                                                                                        "onTheClock" => $onTheClock
                                                                                    ])', ['ticket' => $row, 'onTheClock' => $tpl->get('onTheClock')]); ?>


                                                    <?php if ($row['dependingTicketId'] > 0) { ?>
                                                        <small><a href="#/tickets/showTicket/<?= $row['dependingTicketId'] ?>" class="form-modal"><?= $tpl->escape($row['parentHeadline']) ?></a></small> //
                                                    <?php } ?>
                                                    <small><i class="fa <?php echo $todoTypeIcons[strtolower($row['type'])]; ?>"></i> <?php echo $tpl->__('label.'.strtolower($row['type'])); ?></small>
                                                    <small>#<?php echo $row['id']; ?></small>
                                                    <div class="kanbanCardContent">
                                                        <h4><a href="#/tickets/showTicket/<?php echo $row['id']; ?>" data-hx-get="<?= BASE_URL?>/tickets/showTicket/<?php echo $row['id']; ?>" hx-swap="none" preload="mouseover"><?php $tpl->e($row['headline']); ?></a></h4>

                                                        <div class="kanbanContent" style="margin-bottom: 20px">
                                                            <?php echo $tpl->escapeMinimal($row['description']); ?>
                                                        </div>

                                                    </div>
                                                    <div class="tw:flex">
                                                    <?php if ($row['dateToFinish'] != '0000-00-00 00:00:00' && $row['dateToFinish'] != '1969-12-31 00:00:00') { ?>
                                                        <div>
                                                            <?php echo $tpl->__('label.due_icon'); ?>
                                                            <input type="text" title="<?php echo $tpl->__('label.due'); ?>" value="<?php echo format($row['dateToFinish'])->date() ?>" class="duedates secretInput" style="margin-left:0px;" data-id="<?php echo $row['id']; ?>" name="date" />
                                                        </div>
                                                        <div>
                                                            <?php $tpl->dispatchTplEvent('afterDates', ['ticket' => $row]); ?>
                                                        </div>
                                                    <?php } ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

                                            <div class="timerContainer " id="timerContainer-<?php echo $row['id']; ?>" >

                                                    <div class="dropdown ticketDropdown milestoneDropdown colorized show firstDropdown" >
                                                        <a style="background-color:<?= $tpl->escape($row['milestoneColor'])?>" class="dropdown-toggle f-left  label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text"><?php
                                                        if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                                            $tpl->e($row['milestoneHeadline']);
                                                        } else {
                                                            echo $tpl->__('label.no_milestone');
                                                        }?>
                                                            </span>
                                                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink<?= $row['id']?>">
                                                            <li class="nav-header border"><?= $tpl->__('dropdown.choose_milestone')?></li>
                                                            <li class='dropdown-item'><a style='background-color:#b0b0b0' href='javascript:void(0);' data-label="<?= $tpl->__('label.no_milestone')?>" data-value='<?= $row['id'].'_0_#b0b0b0'?>'> <?= $tpl->__('label.no_milestone')?> </a></li>

                                                            <?php foreach ($tpl->get('milestones') as $milestone) {
                                                                echo "<li class='dropdown-item'>
                                                                    <a href='javascript:void(0);' data-label='".$tpl->escape($milestone->headline)."' data-value='".$row['id'].'_'.$milestone->id.'_'.$tpl->escape($milestone->tags)."' id='ticketMilestoneChange".$row['id'].$milestone->id."' style='background-color:".$tpl->escape($milestone->tags)."'>".$tpl->escape($milestone->headline).'</a>';
                                                                echo '</li>';
                                                            }?>
                                                        </ul>
                                                    </div>


                                                <?php if ($row['storypoints'] != '' && $row['storypoints'] > 0) { ?>
                                                    <div class="dropdown ticketDropdown effortDropdown show">
                                                    <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text"><?php
                                                        if ($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                            echo $efforts[''.$row['storypoints']] ?? $row['storypoints'];
                                                        } else {
                                                            echo $tpl->__('label.story_points_unkown');
                                                        }?>
                                                        </span>
                                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink<?= $row['id']?>">
                                                        <li class="nav-header border"><?= $tpl->__('dropdown.how_big_todo')?></li>
                                                        <?php foreach ($efforts as $effortKey => $effortValue) {
                                                            echo "<li class='dropdown-item'>
                                                                                <a href='javascript:void(0);' data-value='".$row['id'].'_'.$effortKey."' id='ticketEffortChange".$row['id'].$effortKey."'>".$effortValue.'</a>';
                                                            echo '</li>';
                                                        }?>
                                                    </ul>
                                                </div>
                                                <?php } ?>


                                                <div class="dropdown ticketDropdown priorityDropdown show">
                                                    <a class="dropdown-toggle f-left  label-default priority priority-bg-<?= $row['priority']?>" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text"><?php
                                                        if ($row['priority'] != '' && $row['priority'] > 0) {
                                                            echo $priorities[$row['priority']] ?? $tpl->__('label.priority_unkown');
                                                        } else {
                                                            echo $tpl->__('label.priority_unkown');
                                                        }?>
                                                        </span>
                                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="priorityDropdownMenuLink<?= $row['id']?>">
                                                        <li class="nav-header border"><?= $tpl->__('dropdown.select_priority')?></li>
                                                        <?php foreach ($priorities as $priorityKey => $priorityValue) {
                                                            echo "<li class='dropdown-item'>
                                                                                <a href='javascript:void(0);' class='priority-bg-".$priorityKey."' data-value='".$row['id'].'_'.$priorityKey."' id='ticketPriorityChange".$row['id'].$priorityKey."'>".$priorityValue.'</a>';
                                                            echo '</li>';
                                                        }?>
                                                    </ul>
                                                </div>


                                                <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">
                                                            <?php
                                                            if ($row['editorFirstname'] != '') {
                                                                echo "<span id='userImage".$row['id']."'><img src='".BASE_URL.'/api/users?profileImage='.$row['editorId']."' width='25' style='vertical-align: middle;'/></span>";
                                                            } else {
                                                                echo "<span id='userImage".$row['id']."'><img src='".BASE_URL."/api/users?profileImage=false' width='25' style='vertical-align: middle;'/></span>";
                                                            }?>
                                                        </span>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?= $row['id']?>">
                                                        <li class="nav-header border"><?= $tpl->__('dropdown.choose_user')?></li>

                                                        <?php
                                                        if (is_array($tpl->get('users'))) {
                                                            foreach ($tpl->get('users') as $user) {
                                                                echo "<li class='dropdown-item'>
                                                                    <a href='javascript:void(0);' data-label='".sprintf(
                                                                    $tpl->__('text.full_name'),
                                                                    $tpl->escape($user['firstname']),
                                                                    $tpl->escape($user['lastname'])
                                                                )."' data-value='".$row['id'].'_'.$user['id'].'_'.$user['profileId']."' id='userStatusChange".$row['id'].$user['id']."' ><img src='".BASE_URL.'/api/users?profileImage='.$user['id']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".sprintf(
                                                                    $tpl->__('text.full_name'),
                                                                    $tpl->escape($user['firstname']),
                                                                    $tpl->escape($user['lastname'])
                                                                ).'</a>';
                                                                echo '</li>';
                                                            }
                                                        }?>
                                                    </ul>
                                                </div>

                                            </div>
                                            <div class="clearfix"></div>

                                            <?php if ($row['commentCount'] > 0 || $row['subtaskCount'] > 0 || $row['tags'] != '') {?>
                                            <div class="row">

                                                <div class="col-md-12 border-top" style="white-space: nowrap;">
                                                    <?php if ($row['commentCount'] > 0) {?>
                                                        <a href="#/tickets/showTicket/<?php echo $row['id']; ?>"><span class="fa-regular fa-comments"></span> <?php echo $row['commentCount'] ?></a>&nbsp;
                                                    <?php } ?>

                                                    <?php if ($row['subtaskCount'] > 0) {?>
                                                        <a id="subtaskLink_<?php echo $row['id']; ?>" href="#/tickets/showTicket/<?php echo $row['id']; ?>" class="subtaskLineLink"> <span class="fa fa-diagram-successor"></span> <?php echo $row['subtaskCount'] ?></a>&nbsp;
                                                    <?php } ?>
                                                    <?php if ($row['tags'] != '') {?>
                                                        <?php $tagsArray = explode(',', $row['tags']); ?>
                                                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                                                            <i class="fa fa-tags" aria-hidden="true"></i> <?= count($tagsArray)?>
                                                        </a>
                                                        <ul class="dropdown-menu ">
                                                            <li style="padding:10px"><div class='tagsinput readonly'>
                                                            <?php

                                                            foreach ($tagsArray as $tag) {
                                                                echo "<span class='tag'><span>".$tpl->escape($tag).'</span></span>';
                                                            }

                                                        ?>
                                                                </div></li></ul>
                                                    <?php } ?>

                                                </div>

                                            </div>
                                            <?php } ?>

                                        </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>

                            </div>
                        <?php } ?>
                            <div class="clearfix"></div>

                        </div>
                    </div>

            <?php if ($group['label'] != 'all') { ?>
                </div> <!-- .kanban-swimlane-content -->
                </div> <!-- .kanban-swimlane-row -->
            <?php } ?>

        <?php } ?>

    </div>

</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
        leantime.ticketsController.initUserDropdown();
        leantime.ticketsController.initMilestoneDropdown();
        leantime.ticketsController.initDueDateTimePickers();
        leantime.ticketsController.initEffortDropdown();
        leantime.ticketsController.initPriorityDropdown();


        var ticketStatusList = [<?php foreach ($tpl->get('allTicketStates') as $key => $statusRow) {
            echo "'".$key."',";
        }?>];
        leantime.ticketsController.initTicketKanban(ticketStatusList);

    <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");
    <?php } ?>

    leantime.ticketsController.setUpKanbanColumns();

        <?php if (isset($_GET['showTicketModal'])) {
            if ($_GET['showTicketModal'] == '') {
                $modalUrl = '';
            } else {
                $modalUrl = '/'.(int) $_GET['showTicketModal'];
            }
            ?>

        leantime.ticketsController.openTicketModalManually("<?= BASE_URL ?>/tickets/showTicket<?php echo $modalUrl; ?>");
        window.history.pushState({},document.title, '<?= BASE_URL ?>/tickets/showKanban');

        <?php } ?>


        <?php foreach ($allTicketGroups as $group) {

            foreach ($group['items'] as $ticket) {
                if ($ticket['dependingTicketId'] > 0) {
                    ?>
            var startElement =  document.getElementById('subtaskLink_<?= $ticket['dependingTicketId']; ?>');
            var endElement =  document.getElementById('ticket_<?= $ticket['id']; ?>');


            if ( startElement != undefined && endElement != undefined) {

                var startAnchor = LeaderLine.mouseHoverAnchor({
                    element: startElement,
                    showEffectName: 'draw',
                    style: {background: 'none', backgroundColor: 'none'},
                    hoverStyle: {background: 'none', backgroundColor: 'none', cursor: 'pointer'}
                });

                var line<?= $ticket['id'] ?> = new LeaderLine(startAnchor, endElement, {
                    startPlugColor: 'var(--accent1)',
                    endPlugColor: 'var(--accent2)',
                    gradient: true,
                    size: 2,
                    path: "grid",
                    startSocket: 'bottom',
                    endSocket: 'auto'
                });

                jQuery("#ticket_<?= $ticket['id'] ?>").mousedown(function () {

                })
                    .mousemove(function () {

                    })
                    .mouseup(function () {
                        line<?= $ticket['id'] ?>.position();
                    });

                jQuery("#ticket_<?= $ticket['dependingTicketId'] ?>").mousedown(function () {

                    })
                    .mousemove(function () {


                    })
                    .mouseup(function () {
                        line<?= $ticket['id'] ?>.position();

                    });

            }

                <?php }
                }
        } ?>




    });
</script>
