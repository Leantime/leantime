<?php

defined('RESTRICTED') or exit('Restricted access');
use Leantime\Core\Support\FromFormat;

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$hoursFormat = session('usersettings.hours_format', 'decimal');
?>
<script src="<?= BASE_URL ?>/assets/js/app/core/moment.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>/assets/css/daterangepicker.css" />
<script type="text/javascript" src="<?= BASE_URL ?>/assets/js/app/core/daterangepicker.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app/core/datePickers.js"></script>

<style>
.maincontentinner .filterTable .filter-select,
.maincontentinner .filterTable .filter-input {
    height: 30px;
    min-height: 30px;
    padding: 4px 14px;
    border-radius: 20px;
    border: 1px solid var(--main-border-color);
    font-size: 14px;
    line-height: 20px;
    background-color: var(--secondary-background);
    color: var(--primary-font-color);
    max-width: 200px;
    box-sizing: border-box;
}
.maincontentinner .filterTable .filter-select {
    cursor: pointer;
    appearance: auto;
}
.maincontentinner .filterTable .filter-input {
    max-width: 120px;
}
.maincontentinner .filterTable label {
    display: block;
    font-weight: bold;
    margin-bottom: 4px;
    color: var(--primary-font-color);
}
.maincontentinner .filterTable td {
    vertical-align: top;
}
</style>
<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('headline.overview'); ?></h5>
        <h1><?php echo $tpl->__('headline.my_timesheets') ?></h1>
    </div>
</div>
<!-- page header -->


<div class="maincontent">
    <div class="maincontentinner">
        <?php
        echo $tpl->displayNotification();
?>

        <form action="<?php echo BASE_URL ?>/timesheets/showMyList" method="post" id="form" name="form">
            <div class="pull-right">
                <div class="btn-group viewDropDown">
                    <button class="btn dropdown-toggle" data-toggle="dropdown"><?= $tpl->__('links.list_view') ?> <?= $tpl->__('links.view') ?></button>
                    <ul class="dropdown-menu">
                        <li><a href="<?= BASE_URL?>/timesheets/showMy" ><?= $tpl->__('links.week_view') ?></a></li>
                        <li><a href="<?= BASE_URL?>/timesheets/showMyMonthlyTimesheet"><i class="fa fa-calendar-alt" style="margin-right:5px;"></i>Monthly View</a></li>
                        <li><a href="<?= BASE_URL?>/timesheets/showMyList" class="active"><?= $tpl->__('links.list_view') ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="pull-right" style="margin-right:3px;">
                <div id="tableButtons" style="display:inline-block"></div>
            </div>

            <div class="pull-right" style="margin-right:8px; vertical-align: middle;">
                <input type="submit" value="<?php echo $tpl->__('buttons.search'); ?>" class="reload btn btn-primary" style="vertical-align: middle; margin-bottom: 0;" />
            </div>

            <div class="clearfix"></div>
            <div class="headtitle">
            <table cellpadding="10" cellspacing="0" width="100%" class="table dataTable filterTable" style="margin-bottom: 15px;">
                <tr>
                    <td style="vertical-align: top;">
                        <label for="clientId"><?php echo $tpl->__('label.client'); ?></label>
                        <select name="clientId" id="clientId" class="filter-select" onchange="this.form.submit();">
                            <option value="-1"><?php echo strip_tags($tpl->__('menu.all_clients')); ?></option>
                            <?php foreach ($tpl->get('allClients') as $client) { ?>
                                <option value="<?= (int) $client['id'] ?>"
                                    <?php if ($tpl->get('clientFilter') == $client['id']) {
                                        echo ' selected="selected"';
                                    } ?>><?= $tpl->escape($client['name']) ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <label for="projectId"><?php echo $tpl->__('label.project'); ?></label>
                        <select name="projectId" id="projectId" class="filter-select" onchange="this.form.submit();">
                            <option value="-1"><?php echo strip_tags($tpl->__('menu.all_projects')); ?></option>
                            <?php foreach ($tpl->get('allProjects') as $project) { ?>
                                <option value="<?= (int) $project['id'] ?>"
                                    <?php if ($tpl->get('projectFilter') == $project['id']) {
                                        echo ' selected="selected"';
                                    } ?>><?= $tpl->escape($project['name']) ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <label for="dateFrom"><?php echo $tpl->__('label.date_from'); ?></label>
                        <input type="text" id="dateFrom" class="dateFrom filter-input" name="dateFrom" autocomplete="off"
                            value="<?php echo $tpl->get('dateFrom')->formatDateForUser(); ?>"
                            style="max-width:120px; margin-bottom:10px;" />
                    </td>
                    <td style="vertical-align: top;">
                        <label for="dateTo"><?php echo $tpl->__('label.date_to'); ?></label>
                        <input type="text" id="dateTo" class="dateTo filter-input" name="dateTo" autocomplete="off"
                            value="<?php echo $tpl->get('dateTo')->formatDateForUser(); ?>"
                            style="max-width:120px; margin-bottom:10px;" />
                    </td>
                    <td style="vertical-align: top;">
                        <label for="kind"><?php echo $tpl->__('label.type'); ?></label>
                        <select id="kind" name="kind" class="filter-select" onchange="this.form.submit();">
                            <option value="all"><?php echo $tpl->__('label.all_types'); ?></option>
                            <?php foreach ($tpl->get('kind') as $key => $row) {
                                echo '<option value="' . $tpl->escape($key) . '"';
                                if ($key == $tpl->get('actKind')) {
                                    echo ' selected="selected"';
                                }
                                echo '>' . $tpl->escape($tpl->__($row)) . '</option>';
                            } ?>
                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="checkbox" value="1" name="invEmpl" id="invEmpl" onclick="this.form.submit();"
                            <?php if ($tpl->get('invEmpl') == '1') { echo ' checked="checked"'; } ?> />
                        <label for="invEmpl"><?php echo $tpl->__('label.invoiced'); ?></label>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="checkbox" value="on" name="invComp" id="invComp" onclick="this.form.submit();"
                            <?php if ($tpl->get('invComp') == '1') { echo ' checked="checked"'; } ?> />
                        <label for="invComp"><?php echo $tpl->__('label.invoiced_comp'); ?></label>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="checkbox" value="on" name="paid" id="paid" onclick="this.form.submit();"
                            <?php if ($tpl->get('paid') == '1') { echo ' checked="checked"'; } ?> />
                        <label for="paid"><?php echo $tpl->__('label.paid'); ?></label>
                    </td>
                </tr>
            </table>
            </div>

            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered display" id="allTimesheetsTable" data-hours-format="<?= $tpl->escape($hoursFormat); ?>">
                <colgroup>
                      <col class="con0" width="100px"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1"/>
                </colgroup>
                <thead>
                    <tr>
                        <th><?php echo $tpl->__('label.id'); ?></th>
                        <th>Tick.ID</th>
                        <th><?php echo $tpl->__('label.date'); ?></th>
                        <th><?php echo $tpl->__('label.hours'); ?></th>
                        <th><?php echo $tpl->__('label.plan_hours'); ?></th>
                        <th><?php echo $tpl->__('label.difference'); ?></th>
                        <th><?php echo $tpl->__('label.ticket'); ?></th>
                        <th><?php echo $tpl->__('label.project'); ?></th>
                        <th><?php echo $tpl->__('label.employee'); ?></th>
                        <th><?php echo $tpl->__('label.type')?></th>
                        <th><?php echo $tpl->__('label.description'); ?></th>
                        <th><?php echo $tpl->__('label.invoiced'); ?></th>
                        <th><?php echo $tpl->__('label.invoiced_comp'); ?></th>
                        <th><?php echo $tpl->__('label.paid'); ?></th>
                    </tr>

                </thead>
                <tbody>

                <?php

                $sum = 0;
$billableSum = 0;

foreach ($tpl->get('allTimesheets') as $row) {
    $sum = $sum + $row['hours']; ?>
                    <tr>
                        <td data-order="<?php echo $tpl->e($row['id']); ?>">
                            <a href="<?= BASE_URL?>/timesheets/editTime/<?php echo $row['id']?>" class="editTimeModal">#<?php echo $row['id']; ?></a>
                        </td>
                        <td data-order="<?php echo !empty($row['projectKey']) ? $tpl->escape($row['projectKey']) . '-' . $tpl->escape($row['ticketId']) : '#' . $tpl->escape($row['ticketId']); ?>">
                            <a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>">
                                <?php
                                if (!empty($row['projectKey'])) {
                                    echo $tpl->escape($row['projectKey']) . '-' . $tpl->escape($row['ticketId']);
                                } else {
                                    echo '#' . $tpl->escape($row['ticketId']);
                                }
                                ?>
                            </a>
                        </td>
                        <td data-order="<?php echo format($row['workDate'])->isoDateTime(); ?>">
                            <?php echo format($row['workDate'])->date(); ?>
                            <?php echo format($row['workDate'])->time(); ?>
                        </td>
                        <?php /* legacy: <td data-order="<?php $tpl->e($row['hours']); ?>"><?php $tpl->e($row['hours'] ?: 0); ?></td> */ ?>
                        <td data-order="<?php $tpl->e($row['hours']); ?>" data-export-display="<?php echo format_hours($row['hours'] ?: 0); ?>" class="js-timesheet-hours">
                            <?php echo format_hours($row['hours'] ?: 0); ?>
                        </td>
                        <?php /* legacy: <td data-order="<?php $tpl->e($row['planHours']); ?>"><?php $tpl->e($row['planHours'] ?: 0); ?></td> */ ?>
                        <td data-order="<?php $tpl->e($row['planHours']); ?>" data-export-display="<?php echo format_hours($row['planHours'] ?: 0); ?>" class="js-timesheet-hours">
                            <?php echo format_hours($row['planHours'] ?: 0); ?>
                        </td>
                        <?php $diff = ($row['planHours'] ?: 0) - ($row['hours'] ?: 0); ?>
                        <?php /* legacy: <td data-order="<?php echo $diff; ?>"><?php echo $diff; ?></td> */ ?>
                        <td data-order="<?php echo $diff; ?>" data-export-display="<?php echo format_hours($diff); ?>" class="js-timesheet-hours">
                            <?php echo format_hours($diff); ?>
                        </td>
                        <td data-order="<?php echo $tpl->e($row['headline']); ?>">
                            <a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php $tpl->e($row['headline']); ?></a>
                        </td>

                        <td data-order="<?php echo $tpl->e($row['name']); ?>">
                            <a href="<?php echo BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php $tpl->e($row['name']); ?></a>
                        </td>
                        <td>
                            <?php sprintf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?>
                        </td>
                        <td>
                            <?php echo $tpl->__($tpl->get('kind')[$row['kind']]); ?>
                        </td>
                        <td>
                            <?php $tpl->e($row['description']); ?>
                        </td>
                        <td data-order="<?php if ($row['invoicedEmpl'] == '1') {
                            echo format(value: $row['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date();
                        }?>">
                            <?php if ($row['invoicedEmpl'] == '1') {
                                echo format(value: $row['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date();
                            } else {
                                echo $tpl->__('label.pending');
                            } ?>
                        </td>
                        <td data-order="<?php if ($row['invoicedComp'] == '1') {
                            echo format(value: $row['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date();
                        }?>">
                            <?php if ($row['invoicedComp'] == '1') {
                                echo format(value: $row['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date();
                            } else {
                                echo $tpl->__('label.pending');
                            } ?>
                        </td>
                        <td data-order="<?php if ($row['paid'] == '1') {
                            echo format(value: $row['paidDate'], fromFormat: FromFormat::DbDate)->date();
                        }?>">
                            <?php if ($row['paid'] == '1') {
                                echo format(value: $row['paidDate'], fromFormat: FromFormat::DbDate)->date();
                            } else {
                                echo $tpl->__('label.pending');
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td colspan="1"><strong><?php echo $tpl->__('label.total_hours')?></strong></td>
                        <?php /* legacy total: <td colspan="11"><strong><?php echo $sum; ?></strong></td> */ ?>
                        <td colspan="11" class="js-timesheet-hours" data-export-display="<?php echo format_hours($sum); ?>"><strong><?php echo format_hours($sum); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">
    // Initialize CSV export formatter (inline to avoid build dependency)
    window.leantime = window.leantime || {};
    window.leantime.timesheetsExport = window.leantime.timesheetsExport || {};

    if (typeof window.leantime.timesheetsExport.resolveCell !== 'function') {
        window.leantime.timesheetsExport.resolveCell = function ($node, fallbackData) {
            if (typeof $node.data('order') === 'undefined') {
                return fallbackData;
            }

            if (! $node.hasClass('js-timesheet-hours')) {
                return $node.data('order');
            }

            var tableFormat = ($node.closest('table[data-hours-format]').data('hoursFormat') || '').toString();

            if (tableFormat === 'human') {
                // jQuery converts data-export-display to exportDisplay in .data()
                if (typeof $node.data('exportDisplay') !== 'undefined') {
                    return $node.data('exportDisplay');
                }

                return $node.text().trim();
            }

            return $node.data('order');
        };
    }

    jQuery(document).ready(function(){
        leantime.timesheetsController.initTimesheetsTable();
        leantime.timesheetsController.initEditTimeModal();
        leantime.dateController.initModernDateRangePicker(".dateFrom", ".dateTo", 1);
    });
</script>
