<?php

use \leantime\core\events;
use \leantime\domain\models\tickets;

defined('RESTRICTED') or die('Restricted access');

$timeTrackingSummariesAppliedTemplates = [
    'tickets.showAll',
    'tickets.showAllMilestones'
];

foreach ($timeTrackingSummariesAppliedTemplates as $template_location) {
    events::add_event_listener("tpl.$template_location.allTicketsTable.afterBody", 'showTimeTrackingSummaries');
    events::add_event_listener("tpl.$template_location.scripts.afterOpen", 'showTimeTrackingSummariesScripts');
}

function showTimeTrackingSummaries ($params) {
    $tickets = $params['tickets'];

    if ($tickets[0] instanceof tickets) {
        $tickets = array_map(function ($ticket) {
            return [
                'planHours' => $ticket->planHours,
                'hourRemaining' => $ticket->hourRemaining,
                'bookedHours' => $ticket->bookedHours
            ];
        }, $tickets);
    }

    $total_planned = $total_left = $total_logged = 0;

    foreach ($tickets as $ticket) {
        $total_planned += (int) $ticket['planHours'];
        $total_left += (int) $ticket['hourRemaining'];
        $total_logged += (int) $ticket['bookedHours'];
    }

    $colspan = $params['context'] == 'tickets.showAll' ? 9 : 6;

    ob_start();
    ?>
        <!--<tfoot style="background-color: var(--kanban-col-title-bg);">-->
        <tfoot>
            <tr>
                <td scope="row" colspan="<?php echo $colspan; ?>"><strong>Total</strong></td>
                <td><?php echo $total_planned; ?></td>
                <td><?php echo $total_left; ?></td>
                <td><?php echo $total_logged; ?></td>
                <?php if ($params['context'] == 'tickets.showAllMilestones') { ?>
                    <td></td>
                <?php } ?>
            </tr>
        </tfoot>
    <?php
    echo ob_get_clean();
};

function showTimeTrackingSummariesScripts () {
    ob_start();
    require_once __DIR__ . '/../js/summariesAjax.js';
    echo ob_get_clean();
}
