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
                                $swimlaneKey = $group['value'] ?? $group['id'] ?? null;
                                $isEmpty = isset($emptyColumns[$key]);
                                $currentGroupBy = $searchCriteria['groupBy'] ?? null;
                                include __DIR__.'/partials/quickadd-form.inc.php';
                                ?>

                                    <?php foreach ($allTickets as $row) { ?>
                                        <?php if ($row['status'] == $key) {?>
                                        <div class="ticketBox moveable container priority-border-<?= $row['priority']?>" id="ticket_<?php echo $row['id']; ?>">

                                            <div class="kanbanCardContent" style="margin-bottom:8px;">
                                                <a href="#/tickets/showTicket/<?php echo $row['id']; ?>"
                                                    preload="mouseover"
                                                    style="font-weight:600; font-size:var(--base-font-size); color:var(--primary-font-color); text-decoration:none; display:block; line-height:1.4;">
                                                    <?php $tpl->e($row['headline']); ?>
                                                </a>
                                            </div>

                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:6px;">
                                                <?php if ($row['dateToFinish'] != '0000-00-00 00:00:00' && $row['dateToFinish'] != '1969-12-31 00:00:00') { ?>
                                                    <span style="font-size:var(--font-size-xs); color:var(--primary-font-color); opacity:.7;">
                                                        <i class="fa-solid fa-calendar-days" style="margin-right:3px;"></i><?php echo format($row['dateToFinish'])->date(); ?>
                                                    </span>
                                                <?php } else { ?>
                                                    <span></span>
                                                <?php } ?>

                                                <?php if ($row['priority'] != '' && $row['priority'] > 0) { ?>
                                                    <span class="priority-bg-<?= $row['priority'] ?>"
                                                        style="font-size:var(--font-size-xs); padding:2px 8px; border-radius:var(--element-radius); font-weight:500;">
                                                        <?php echo $priorities[$row['priority']] ?? ''; ?>
                                                    </span>
                                                <?php } ?>
                                            </div>

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
