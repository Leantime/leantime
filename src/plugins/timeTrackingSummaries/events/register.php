<?php
/**
 * Time Tracking Summaries (Example Plugin)
 * 
 * Adds Time Tracking Summaries to /tickets/showAll
 */

use \leantime\core\events;

defined('RESTRICTED') or die('Restricted access');

if (!defined('PLUGIN_TIMETRACKINGSUMMARIES_ENABLED')
    || PLUGIN_TIMETRACKINGSUMMARIES_ENABLED !== true
) {
    return false;
}

events::add_event_listener(
    "tpl.tickets.showAll.afterTableBody", 
    function ($eventname, $params) {
        if ($params['context'] !== 'tickets.showAll') {
            return false;
        }

        $tickets = $params['tickets'];
        $total_planned = $total_left = $total_logged = 0;

        foreach ($tickets as $ticket) {
            $total_planned += (int) $ticket['planHours'];
            $total_left += (int) $ticket['hourRemaining'];
            $total_logged += (int) $ticket['bookedHours'];
        }

        ob_start();
        ?>
            <!--<tfoot style="background-color: var(--kanban-col-title-bg);">-->
            <tfoot>
                <tr>
                    <td scope="row" colspan="9"><strong>Total</strong></td>
                    <td><?php echo $total_planned; ?></td>
                    <td><?php echo $total_left; ?></td>
                    <td><?php echo $total_logged; ?></td>
                </tr>
            </tfoot>
        <?php
        echo ob_get_clean();
    }
);

events::add_event_listener(
    "tpl.tickets.showAll.afterScriptsStart", 
    function () {
        ob_start();
        require_once __DIR__ . '/../js/summariesAjax.js';
        echo ob_get_clean();
    }
);
