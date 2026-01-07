<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}

/** @var \Carbon\Carbon $currentDate */
$dateFrom = $tpl->get('dateFrom');
$hoursFormat = session('usersettings.hours_format', 'decimal');

?>
<script type="text/javascript">
jQuery(document).ready(function() {
    var startDate;
    var endDate;
    
    // Initialize datepicker dates from server values
    var initStartDate = jQuery('#startDate').val();
    var initEndDate = jQuery('#endDate').val();
    
    jQuery('.month-picker').datepicker({
        dateFormat: 'yy-mm-dd', // Use consistent format
        
        dayNames: leantime.i18n.__("language.dayNames").split(","),
        dayNamesMin: leantime.i18n.__("language.dayNamesMin").split(","),
        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),

        monthNames: leantime.i18n.__("language.monthNames").split(","),
        monthNamesShort: leantime.i18n.__("language.monthNamesShort").split(","),

        currentText: leantime.i18n.__("language.currentText"),
        closeText: leantime.i18n.__("language.closeText"),
        buttonText: leantime.i18n.__("language.buttonText"),
        nextText: leantime.i18n.__("language.nextText"),
        prevText: leantime.i18n.__("language.prevText"),
        weekHeader: leantime.i18n.__("language.weekHeader"),

        isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,

        firstDay: 1,
        autoSize: true,
        showOtherMonths: true,
        selectOtherMonths: true,

        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,

        onSelect: function(dateText, inst) {
            jQuery(this).change();
            jQuery("#timesheetList").submit();
        }
    });

    // Set initial dates from server
    if (initStartDate) {
        jQuery('#startDate').datepicker('setDate', initStartDate);
    }
    if (initEndDate) {
        jQuery('#endDate').datepicker('setDate', initEndDate);
    }

    jQuery("#nextMonth").click(function() {
        // Get the current date from the startDate field
        var currentDate = jQuery("#startDate").datepicker('getDate');
        
        if (!currentDate) {
            currentDate = new Date();
        }
        
        // Calculate next month (first day)
        var nextMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
        
        // Format as YYYY-MM-DD
        var startDateStr = nextMonthDate.getFullYear() + '-' + 
                          String(nextMonthDate.getMonth() + 1).padStart(2, '0') + '-01';
        
        // Last day of next month
        var lastDay = new Date(nextMonthDate.getFullYear(), nextMonthDate.getMonth() + 1, 0);
        var endDateStr = lastDay.getFullYear() + '-' + 
                        String(lastDay.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(lastDay.getDate()).padStart(2, '0');

        jQuery('#startDate').val(startDateStr);
        jQuery('#endDate').val(endDateStr);
        jQuery("#timesheetList").submit();
    });

    jQuery("#prevMonth").click(function() {
        // Get the current date from the startDate field
        var currentDate = jQuery("#startDate").datepicker('getDate');
        
        if (!currentDate) {
            currentDate = new Date();
        }
        
        // Calculate previous month (first day)
        var prevMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
        
        // Format as YYYY-MM-DD
        var startDateStr = prevMonthDate.getFullYear() + '-' + 
                          String(prevMonthDate.getMonth() + 1).padStart(2, '0') + '-01';
        
        // Last day of previous month
        var lastDay = new Date(prevMonthDate.getFullYear(), prevMonthDate.getMonth() + 1, 0);
        var endDateStr = lastDay.getFullYear() + '-' + 
                        String(lastDay.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(lastDay.getDate()).padStart(2, '0');

        jQuery('#startDate').val(startDateStr);
        jQuery('#endDate').val(endDateStr);
        jQuery("#timesheetList").submit();
    });

        // =======================
        // 1. Build monthly table dynamically
        // =======================
        function buildMonthTable(date) {
            var year = date.getFullYear();
            var month = date.getMonth();
            var daysInMonth = new Date(year, month + 1, 0).getDate();

            var $table = jQuery("#timesheetTable");

            // 1. Generate table headers
            var $theadRow = $table.find("thead tr");
            $theadRow.find("th:gt(0)").remove(); // remove old headers
            for (var d = 1; d <= daysInMonth; d++) {
                $theadRow.append('<th class="day' + d + '">' + d + '</th>');
            }

            // 2. Generate input cells for each row
            $table.find("tbody .timesheetRow").each(function() {
                var $row = jQuery(this);
                $row.find("td:gt(0)").remove(); // remove old cells

                for (var d = 1; d <= daysInMonth; d++) {
                    $row.append('<td class="rowday' + d + '"><input type="number" class="hourCell" min="0" step="0.25" value="0"></td>');
                }

                // Add row sum column if not exists
                if ($row.find(".rowSum").length === 0) {
                    $row.append('<td class="rowSum"><strong>0</strong></td>');
                }
            });

            // 3. Generate footer daily totals
            var $dailyTotalRow = $table.find("tfoot tr:first");
            $dailyTotalRow.find("td:gt(0)").remove(); // remove old totals

            for (var d = 1; d <= daysInMonth; d++) {
                $dailyTotalRow.append('<td id="day' + d + '">0</td>');
            }
        }

        // =======================
        // 2. Calculate sums on input change
        // =======================
        jQuery(document).on("change", ".timesheetTable input.hourCell", function() {
            var $table = jQuery("#timesheetTable");
            var daysInMonth = $table.find("thead th").length - 1; // minus Task column

            var colSums = Array(daysInMonth).fill(0);
            var finalSum = 0;

            // Loop rows
            $table.find(".timesheetRow").each(function() {
                var rowSum = 0;

                jQuery(this).find("input.hourCell").each(function(index) {
                    var val = parseFloat(jQuery(this).val()) || 0;
                    rowSum += val;
                    colSums[index] += val;
                });

                rowSum = Math.round(rowSum * 100) / 100;
                jQuery(this).find(".rowSum strong").text(rowSum);
                finalSum += rowSum;
            });

            // Update daily totals
            colSums.forEach((sum, index) => {
                jQuery("#day" + (index + 1)).text(sum.toFixed(2));
            });

            // Update final sum
            jQuery("#finalSum").text(Math.round(finalSum * 100) / 100);
        });

        // =======================
        // 3. Initialize with current month
        // =======================
        jQuery(document).ready(function() {
            var selectedDate = new Date();
            buildMonthTable(selectedDate);

            // Optional: if user is manager, init edit modal
            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                leantime.timesheetsController.initEditTimeModal();
            <?php } ?>
        });
    });
</script>

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('headline.overview'); ?></h5>
        <h1><?php echo $tpl->__('headline.my_timesheets'); ?></h1>
    </div>
</div>
<!-- page header -->

<div class="maincontent">
    <div class="maincontentinner">
        <?php
        echo $tpl->displayNotification();

        ?>

        <form action="<?php echo BASE_URL ?>/timesheets/showMyMonthlyTimesheet" method="post" id="timesheetList">
            <div class="btn-group viewDropDown pull-right">
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                    <?php echo "Monthly view" ?> <?= $tpl->__('links.view') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo BASE_URL ?>/timesheets/showMy" class="active"><?php echo $tpl->__('links.week_view') ?></a></li>
                    <li><a href="<?php echo BASE_URL ?>/timesheets/showMyList"><?php echo $tpl->__('links.list_view') ?></a></li>
                    <li><a href="<?= BASE_URL ?>/timesheets/showMyMonthlyTimesheet" class="active">Monthly View</a></li>
                </ul>
            </div>
            <div class="pull-left" style="padding-left:5px; margin-top:-3px;">

<div class="padding-top-sm">
    <a href="javascript:void(0)" style="font-size:16px;" id="prevMonth"><i class="fa fa-chevron-left"></i></a>
    <input type="text" class="month-picker" name="startDate" autocomplete="off" id="startDate" placeholder="<?php echo $tpl->__('language.dateformat') ?>" value="<?php echo $dateFrom->copy()->startOfMonth()->format('Y-m-d') ?>" style="margin-top:5px;" />
    <?php echo $tpl->__('label.until'); ?>
    <input type="text" class="month-picker" name="endDate" autocomplete="off" id="endDate" placeholder="<?php echo $tpl->__('language.dateformat') ?>" value="<?php echo $dateFrom->copy()->endOfMonth()->format('Y-m-d') ?>" style="margin-top:6px;" />
    <a href="javascript:void(0)" style="font-size:16px;" id="nextMonth"><i class="fa fa-chevron-right"></i></a>
    <input type="hidden" name="search" value="1" />
</div>

            </div>
            <div style=" width: 100%; overflow-x:scroll;">
                <table cellpadding="0" width="100%" class="table table-bordered display timesheetTable" id="dyntableX" data-hours-format="<?= $tpl->escape($hoursFormat); ?>">
                    <colgroup>
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
                    <thead>
                        <?php
                        $dateFrom = $tpl->get('dateFrom');
                        $daysInMonth = $dateFrom->daysInMonth();
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            echo "<th>{$d}</th>";
                        }
                        ?>
                        <tr>
                            <th><?php echo $tpl->__('label.client_product') ?></th>
                            <th><?php echo $tpl->__('subtitles.todo') ?></th>
                            <th><?php echo $tpl->__('label.type') ?></th>
                            <th><?php echo $tpl->__('label.total') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // @todo: move all this calculations into the service the timesheets class.
                        foreach ($tpl->get('allTimesheets') as $timeRow) {
                            $timesheetId = 'new';
                        ?>
                            <tr class="gradeA timesheetRow">
                                <td width="14%"><?php $tpl->e($timeRow['clientName']); ?> // <?php $tpl->e($timeRow['name']); ?></td>
                                <td width="14%">
                                    <a href="#/tickets/showTicket/<?php echo $timeRow['ticketId']; ?>"><?php $tpl->e($timeRow['headline']); ?></a>
                                </td>
                                <td width="10%">
                                    <?php
                                    echo $tpl->__($tpl->get('kind')[$timeRow['kind'] ?? 'GENERAL_BILLABLE'] ?? $tpl->get('kind')['GENERAL_BILLABLE']); ?>
                                    <?php if ($timeRow['hasTimesheetOffset']) { ?>
                                        <i class="fa-solid fa-clock-rotate-left pull-right label-blue"
                                            data-tippy-content="This entry was likely created using a different timezone. Only existing entries can be updated in this timezone">
                                        </i>
                                    <?php } ?>
                                </td>

                                <?php foreach (array_keys($timeRow) as $dayKey) {
                                    if (str_starts_with($dayKey, 'day')) {
                                        $colSum[$dayKey] = ($colSum[$dayKey] ?? 0) + $timeRow[$dayKey]['hours']; ?>

                                        <td width="7%" class="row<?php
                                                                    echo $dayKey;
                                                                    if ($timeRow[$dayKey]['start']->setToUserTimezone()->isToday()) {
                                                                        echo ' active';
                                                                    }
                                                                    ?>">


                                            <?php
                                            $inputNameKey = $timeRow['ticketId'] . '|' . $timeRow['kind'] . '|' . ($timeRow[$dayKey]['actualWorkDate'] ? $timeRow[$dayKey]['actualWorkDate']->formatDateForUser() : 'false') . '|' . ($timeRow[$dayKey]['actualWorkDate'] ? $timeRow[$dayKey]['actualWorkDate']->getTimestamp() : 'false');
                                            ?>
                                            <input type="text"
                                                class="hourCell"
                                                <?php if (empty($timeRow[$dayKey]['actualWorkDate'])) {
                                                    echo "disabled='disabled'";
                                                } ?>
                                                name="<?php echo $inputNameKey ?>"
                                                value="<?php echo format_hours($timeRow[$dayKey]['hours']); ?>"
                                                data-decimal-value="<?php echo $timeRow[$dayKey]['hours']; ?>"
                                                <?php if (empty($timeRow[$dayKey]['actualWorkDate'])) { ?>
                                                data-tippy-content="Cannot add time entry in previous timezone"
                                                <?php } ?> />

                                            <?php if (! empty($timeRow[$dayKey]['description'])) { ?>
                                                <a href="<?= BASE_URL ?>/timesheets/editTime/<?= $timeRow[$dayKey]['id'] ?>" class="editTimeModal">
                                                    <i class="fa fa-circle-info" data-tippy-content="<?php echo $tpl->escape($timeRow[$dayKey]['description']); ?>"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                <?php
                                    }
                                } ?>

                                <td width="7%" class="rowSum" data-order="<?php echo $timeRow['rowSum']; ?>"><strong><?php echo format_hours($timeRow['rowSum']); ?></strong></td>
                            </tr>
                        <?php } ?>

                        <!-- Row to add new time registration -->
                        <tr class="gradeA timesheetRow">
                            <td width="14%">
                                <div class="form-group" id="projectSelect">
                                    <select data-placeholder="<?php echo $tpl->__('input.placeholders.choose_project') ?>" style="" class="project-select">
                                        <option value=""></option>
                                        <?php foreach ($tpl->get('allProjects') as $projectRow) { ?>
                                            <?php echo sprintf(
                                                $tpl->dispatchTplFilter(
                                                    'client_product_format',
                                                    '<option value="%s">%s / %s</option>'
                                                ),
                                                ...$tpl->dispatchTplFilter(
                                                    'client_product_values',
                                                    [
                                                        $projectRow['id'],
                                                        $tpl->escape($projectRow['clientName']),
                                                        $tpl->escape($projectRow['name']),
                                                    ]
                                                )
                                            ); ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </td>
                            <td width="14%">
                                <div class="form-group" id="ticketSelect">
                                    <select data-placeholder="<?php echo $tpl->__('input.placeholders.choose_todo') ?>" style="" class="ticket-select" name="ticketId">
                                        <option value=""></option>
                                        <?php foreach ($tpl->get('allTickets') as $ticketRow) {
                                            if (in_array($ticketRow['id'], $tpl->get('existingTicketIds'))) {
                                                continue;
                                            }
                                        ?>
                                            <?php echo sprintf(
                                                $tpl->dispatchTplFilter(
                                                    'todo_format',
                                                    '<option value="%1$s" data-value="%2$s" class="project_%2$s">%1$s / %3$s</option>'
                                                ),
                                                ...$tpl->dispatchTplFilter(
                                                    'todo_values',
                                                    [
                                                        $ticketRow['id'],
                                                        $ticketRow['projectId'],
                                                        $tpl->escape($ticketRow['headline']),
                                                    ]
                                                )
                                            ); ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </td>
                            <td width="14%">
                                <select class="kind-select" name="kindId">
                                    <?php foreach ($tpl->get('kind') as $key => $kindRow) { ?>
                                        <?php echo '<option value=' . $key . '>' . $tpl->__($kindRow) . '</option>'; ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr style="font-weight:bold;">
                            <td colspan="3"><?php echo $tpl->__('label.total') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="right">
                <input type="submit" name="saveTimeSheet" class="saveTimesheetBtn" value="Save" />
            </div>
            <div class="clearall"></div>
        </form>
    </div>
</div>