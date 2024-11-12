<?php

defined('RESTRICTED') or exit('Restricted access');
use Leantime\Core\Support\FromFormat;

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

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
            <div class="filterWrapper tw-relative">
                <a onclick="jQuery('.filterBar').toggle();" class="btn btn-default pull-left"><?php echo $tpl->__('links.filter') ?> (1)</a>
                <div class="filterBar" style="display:none; top:30px;">

                    <div class="filterBoxLeft">
                        <label for="dateFrom"><?php echo $tpl->__('label.date_from'); ?> <?php echo $tpl->__('label.date_to'); ?></label>
                        <input type="text"
                               id="dateFrom"
                               class="dateFrom"
                               name="dateFrom"
                               value="<?php echo $tpl->get('dateFrom')->formatDateForUser(); ?>"
                               style="margin-bottom:10px; width:90px; float:left; margin-right:10px"/>
                        <input type="text"
                               id="dateTo"
                               class="dateTo"
                               name="dateTo"
                               value="<?php echo $tpl->get('dateTo')->formatDateForUser(); ?>"
                               style="margin-bottom:10px; width:90px" />
                    </div>

                    <div class="filterBoxLeft">
                        <label for="kind"><?php echo $tpl->__('label.type')?></label>
                        <select id="kind" name="kind" onchange="submit();">
                            <option value="all"><?php echo $tpl->__('label.all_types'); ?></option>
                            <?php foreach ($tpl->get('kind') as $key => $row) {
                                echo '<option value="'.$key.'"';
                                if ($key == $tpl->get('actKind')) {
                                    echo ' selected="selected"';
                                }
                                echo '>'.$tpl->__($row).'</option>';
                            }
?>

                        </select>
                    </div>
                    <div class="filterBoxLeft">
                        <label>&nbsp;</label>
                        <input type="submit" value="<?php echo $tpl->__('buttons.search')?>" class="reload" />
                    </div>
                    <div class="clearall"></div>
                </div>
            </div>
            <div class="pull-right">
                <div class="btn-group viewDropDown">
                    <button class="btn dropdown-toggle" data-toggle="dropdown"><?= $tpl->__('links.list_view') ?> <?= $tpl->__('links.view') ?></button>
                    <ul class="dropdown-menu">
                        <li><a href="<?= BASE_URL?>/timesheets/showMy" ><?= $tpl->__('links.week_view') ?></a></li>
                        <li><a href="<?= BASE_URL?>/timesheets/showMyList" class="active"><?= $tpl->__('links.list_view') ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="pull-right" style="margin-right:3px;">
                <div id="tableButtons" style="display:inline-block"></div>
            </div>

            <div class="clearfix"></div>

            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered display" id="allTimesheetsTable">
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
                            <a href="<?= BASE_URL?>/timesheets/editTime/<?php echo $row['id']?>" class="editTimeModal" id="editTimesheet-<?php echo $row['id']?>">#<?php echo $row['id'].' - '.$tpl->__('label.edit'); ?> </a></td>
                        <td data-order="<?php echo format($row['workDate'])->date(); ?>">
                            <?php echo format($row['workDate'])->date(); ?>
                            <?php echo format($row['workDate'])->time(); ?>
                        </td>
                        <td data-order="<?php $tpl->e($row['hours']); ?>">
                            <?php $tpl->e($row['hours'] ?: 0); ?>
                        </td>
                        <td data-order="<?php $tpl->e($row['planHours']); ?>">
                            <?php $tpl->e($row['planHours'] ?: 0); ?>
                        </td>
                        <?php $diff = ($row['planHours'] ?: 0) - ($row['hours'] ?: 0); ?>
                        <td data-order="<?php echo $diff; ?>">
                            <?php echo $diff; ?>
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
                        <td colspan="1"><strong><?php echo $tpl->__('label.total_hours')?></strong></td>
                        <td colspan="11"><strong><?php echo $sum; ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        leantime.timesheetsController.initTimesheetsTable();
        leantime.timesheetsController.initEditTimeModal();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1);
    });
</script>
