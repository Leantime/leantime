<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val;
}

$dateFrom = $tpl->get('dateFrom');
$daysInMonth = $dateFrom->daysInMonth();
$hoursFormat = session('usersettings.hours_format', 'decimal');

?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var initStartDate = jQuery('#startDate').val();
        var initEndDate = jQuery('#endDate').val();

        jQuery('.month-picker').datepicker({
            dateFormat: 'yy-mm-dd',
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin: leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            monthNamesShort: leantime.i18n.__("language.monthNamesShort").split(","),
            isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
            firstDay: 1,
            autoSize: true,
            showOtherMonths: true,
            selectOtherMonths: true,
            changeMonth: true,
            changeYear: true,
            onClose: function(dateText, inst) {
                var month = jQuery("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = jQuery("#ui-datepicker-div .ui-datepicker-year :selected").val();
                jQuery(this).data('datepicker-open', false);
                jQuery(this).datepicker('setDate', new Date(year, month, 1));
                jQuery(this).change();
                jQuery("#timesheetList").submit();
            },
            beforeShow: function(input, inst) {
                jQuery("#ui-datepicker-div").addClass('hide-calendar');
                jQuery(this).data('datepicker-open', true);
            },
            onChangeMonthYear: function(year, month, inst) {
                if (jQuery(this).data('datepicker-open')) {
                    var startDateStr = year + '-' + String(month).padStart(2, '0') + '-01';
                    var lastDay = new Date(year, month, 0);
                    var endDateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(lastDay.getDate()).padStart(2, '0');

                    jQuery('#startDate').val(startDateStr);
                    jQuery('#endDate').val(endDateStr);

                    jQuery("#timesheetList").submit();
                }
            },
        });

        if (initStartDate) {
            jQuery('#startDate').datepicker('setDate', initStartDate);
        }
        if (initEndDate) {
            jQuery('#endDate').datepicker('setDate', initEndDate);
        }

        jQuery("#nextMonth").click(function() {
            var currentDate = jQuery("#startDate").datepicker('getDate');
            if (!currentDate) {
                currentDate = new Date();
            }

            var nextMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
            var startDateStr = nextMonthDate.getFullYear() + '-' +
                String(nextMonthDate.getMonth() + 1).padStart(2, '0') + '-01';

            var lastDay = new Date(nextMonthDate.getFullYear(), nextMonthDate.getMonth() + 1, 0);
            var endDateStr = lastDay.getFullYear() + '-' +
                String(lastDay.getMonth() + 1).padStart(2, '0') + '-' +
                String(lastDay.getDate()).padStart(2, '0');

            jQuery('#startDate').val(startDateStr);
            jQuery('#endDate').val(endDateStr);
            jQuery("#timesheetList").submit();
        });

        jQuery("#prevMonth").click(function() {
            var currentDate = jQuery("#startDate").datepicker('getDate');
            if (!currentDate) {
                currentDate = new Date();
            }

            var prevMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            var startDateStr = prevMonthDate.getFullYear() + '-' +
                String(prevMonthDate.getMonth() + 1).padStart(2, '0') + '-01';

            var lastDay = new Date(prevMonthDate.getFullYear(), prevMonthDate.getMonth() + 1, 0);
            var endDateStr = lastDay.getFullYear() + '-' +
                String(lastDay.getMonth() + 1).padStart(2, '0') + '-' +
                String(lastDay.getDate()).padStart(2, '0');

            jQuery('#startDate').val(startDateStr);
            jQuery('#endDate').val(endDateStr);
            jQuery("#timesheetList").submit();
        });

        jQuery(".timesheetTable input.hourCell").change(function() {
            var daysInMonth = parseInt(jQuery(".timesheetTable").data('days-in-month'));

            let colSums = {};
            for (let d = 1; d <= daysInMonth; d++) {
                colSums['day' + d] = 0;
            }

            jQuery(".timesheetRow").each(function(i) {
                var rowSum = 0;

                jQuery(this).find("input.hourCell").each(function() {
                    if (jQuery(this).is(':disabled')) return;

                    var currentValue = parseFloat(jQuery(this).val());
                    rowSum = Math.round((rowSum + currentValue) * 100) / 100;

                    var currentClass = jQuery(this).parent().attr('class');
                    for (let d = 1; d <= daysInMonth; d++) {
                        if (currentClass.indexOf("rowday" + d) > -1) {
                            colSums['day' + d] = colSums['day' + d] + currentValue;
                            break;
                        }
                    }
                });

                jQuery(this).find(".rowSum strong").text(format_hours(rowSum));
            });

            var finalSum = 0;
            for (let d = 1; d <= daysInMonth; d++) {
                var dayKey = 'day' + d;
                var colTotal = Math.round(colSums[dayKey] * 100) / 100;
                finalSum += colTotal;

                if (colTotal > 0) {
                    jQuery("#" + dayKey).text(format_hours(colTotal));
                } else {
                    jQuery("#" + dayKey).text('');
                }
                jQuery("#" + dayKey).attr('data-decimal', colTotal);
            }

            var roundedSum = Math.round(finalSum * 100) / 100;
            jQuery("#finalSum").text(format_hours(roundedSum));
            jQuery("#finalSum").attr('data-decimal', roundedSum);
        });

        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
            leantime.timesheetsController.initEditTimeModal();
        <?php } ?>
    });
</script>
<style>
    .hide-calendar .ui-datepicker-calendar {
        display: none;
    }

    .ui-datepicker-prev {
        display: none !important;
    }

    .ui-datepicker-next {
        display: none !important;
    }

    .ui-datepicker-header.ui-widget-header.ui-helper-clearfix.ui-corner-all {
        background: white !important;
        max-height: 40px;
    }

    .ui-datepicker-month {
        margin-right: 20px;
    }

    .ui-datepicker-title {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="pageheader">
    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('headline.overview'); ?></h5>
        <h1><?php echo $tpl->__('headline.my_timesheets'); ?></h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        <?php
        echo $tpl->displayNotification();

        ?>

        <form action="<?php echo BASE_URL ?>/timesheets/showMyMonthlyTimesheet" method="post" id="timesheetList">
            <div class="btn-group viewDropDown pull-right">
                <div style="position: relative; display: inline-block;">
    <button style="cursor: pointer; background: none; border: none; font-size: 18px;margin-top:5px;">
        &#x24D8;
        <span style="
            visibility: hidden;
            opacity: 0;
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            white-space: nowrap;
            font-size: 14px;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 1000;
        ">
            You can export your timesheet in the <strong>List view</strong>.
        </span>
    </button>
</div>

<style>
button:hover span {
    visibility: visible !important;
    opacity: 1 !important;
}
</style>
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-calendar-alt" style="margin-right:5px;"></i><?php echo "Monthly view" ?> <?= $tpl->__('links.view') ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo BASE_URL ?>/timesheets/showMy"><?php echo $tpl->__('links.week_view') ?></a></li>
                    <li><a href="<?= BASE_URL ?>/timesheets/showMyMonthlyTimesheet" class="active"><i class="fa fa-calendar-alt" style="margin-right:5px;"></i>Monthly View</a></li>
                    <li><a href="<?php echo BASE_URL ?>/timesheets/showMyList"><?php echo $tpl->__('links.list_view') ?></a></li>
                </ul>
            </div>
            <div class="pull-left" style="padding-left:5px; margin-top:-3px;">

                <div class="padding-top-sm">
                    <?php echo "Month from"; ?>
                    <a href="javascript:void(0)" style="font-size:16px;" id="prevMonth"><i class="fa fa-chevron-left"></i></a>
                    <input type="text" class="month-picker" name="startDate" autocomplete="off" id="startDate" placeholder="<?php echo $tpl->__('language.dateformat') ?>" value="<?php echo $dateFrom->copy()->startOfMonth()->format('Y-m-d') ?>" style="margin-top:5px; width:100px;" />
                    <?php echo $tpl->__('label.until'); ?>
                    <input type="text" class="month-picker" name="endDate" autocomplete="off" id="endDate" placeholder="<?php echo $tpl->__('language.dateformat') ?>" value="<?php echo $dateFrom->copy()->endOfMonth()->format('Y-m-d') ?>" style="margin-top:6px; width:100px;" />
                    <a href="javascript:void(0)" style="font-size:16px;" id="nextMonth"><i class="fa fa-chevron-right"></i></a>
                    <input type="hidden" name="search" value="1" />
                </div>

            </div>
            <div style=" width: 100%; overflow-x:scroll;">
                <table cellpadding="0" width="100%" class="table table-bordered display timesheetTable" id="dyntableX" data-hours-format="<?= $tpl->escape($hoursFormat); ?>" data-days-in-month="<?= $daysInMonth ?>">
                    <thead>
                        <tr>
                            <th><?php echo $tpl->__('label.client_product') ?></th>
                            <th><?php echo $tpl->__('subtitles.todo') ?></th>
                            <th><?php echo $tpl->__('label.type') ?></th>
                            <?php
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $dayDate = $dateFrom->copy()->setDay($d);
                                echo '<th>' . $dayDate->format('j.n.Y.') . '</th>';
                            }
                            ?>
                            <th><?php echo $tpl->__('label.total') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($tpl->get('allTimesheets') as $timeRow) {
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

                                <?php
                                for ($d = 1; $d <= $daysInMonth; $d++) {
                                    $dayKey = 'day' . $d;
                                    $dayData = $timeRow[$dayKey] ?? null;

                                    if ($dayData) {
                                        $colSum[$dayKey] = ($colSum[$dayKey] ?? 0) + $dayData['hours'];
                                    }
                                ?>
                                    <td class="row<?php echo $dayKey; ?><?php if ($dayData && $dayData['start']->setToUserTimezone()->isToday()) {
                                                                            echo ' active';
                                                                        } ?>">
                                        <div style="display:flex; gap:10px; align-items:center;">
                                            <?php if ($dayData && !empty($dayData['actualWorkDate'])) {
                                                $inputNameKey = $timeRow['ticketId'] . '|' . $timeRow['kind'] . '|' . $dayData['actualWorkDate']->formatDateForUser() . '|' . $dayData['actualWorkDate']->getTimestamp();
                                            ?>
                                                <input type="text"
                                                    class="hourCell"
                                                    style="width: 70px;"
                                                    name=" <?php echo $inputNameKey ?>"
                                                    value="<?php echo format_hours($dayData['hours']); ?>"
                                                    data-decimal-value="<?php echo $dayData['hours']; ?>" />

                                                <?php if (!empty($dayData['description'])) { ?>
                                                    <a href="<?= BASE_URL ?>/timesheets/editTime/<?= $dayData['id'] ?>" class="editTimeModal">
                                                        <i class="fa fa-circle-info" data-tippy-content="<?php echo $tpl->escape($dayData['description']); ?>"></i>
                                                    </a>
                                                <?php } ?>
                                        </div>
                                    <?php } else { ?>
                                        <input type="text"
                                            class="hourCell"
                                            disabled='disabled'
                                            value="0"
                                            data-tippy-content="Cannot add time entry in previous timezone" />
                                    <?php } ?>
                                    </td>
                                <?php } ?>

                                <td class="rowSum" data-order="<?php echo $timeRow['rowSum']; ?>"><strong><?php echo format_hours($timeRow['rowSum']); ?></strong></td>
                            </tr>
                        <?php } ?>

                        <tr class="gradeA timesheetRow">
                            <td width="14%">
                                <div class="form-group" id="projectSelect">
                                    <select data-placeholder="<?php echo $tpl->__('input.placeholders.choose_project') ?>" style="width:170px;" class="project-select">
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
                                    <select data-placeholder="<?php echo $tpl->__('input.placeholders.choose_todo') ?>" style="width:170px;" class="ticket-select" name="ticketId">
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
                                <select class="kind-select" name="kindId" style="width:170px;">
                                    <?php foreach ($tpl->get('kind') as $key => $kindRow) { ?>
                                        <?php echo '<option value=' . $key . '>' . $tpl->__($kindRow) . '</option>'; ?>
                                    <?php } ?>
                                </select>
                            </td>
                            <?php
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $dayDate = $dateFrom->copy()->setDay($d);
                            ?>
                                <td width="7%" class="rowday<?php echo $d; ?><?php if ($dayDate->setToUserTimezone()->isToday()) {
                                                                                    echo ' active';
                                                                                } ?>">
                                    <input type="text"
                                        class="hourCell"
                                        style="width:70px;"
                                        name="new|GENERAL_BILLABLE|<?php echo $dayDate->formatDateForUser() ?>|<?php echo $dayDate->getTimestamp() ?>"
                                        value="<?php echo format_hours(0); ?>"
                                        data-decimal-value="0" />
                                </td>
                            <?php } ?>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr style="font-weight:bold;">
                            <td colspan="3"><?php echo $tpl->__('label.total') ?></td>
                            <?php
                            $totalHours = 0;
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $dayKey = 'day' . $d;
                                $col = $colSum[$dayKey] ?? 0;
                                $totalHours += $col;
                            ?>
                                <td id="<?php echo $dayKey ?>" style="padding-left:15px;">
                                    <?php if ($col > 0) {
                                        echo format_hours($col);
                                    } ?>
                                </td>
                            <?php } ?>
                            <td id="finalSum"><?php echo format_hours($totalHours); ?></td>
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