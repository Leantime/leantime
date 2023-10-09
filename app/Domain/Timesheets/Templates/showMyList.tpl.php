<?php

defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>


<div class="pageheader">


    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('headline.overview'); ?></h5>
        <h1><?php echo $tpl->__("headline.my_timesheets") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php
        echo $tpl->displayNotification();
        ?>


        <form action="<?=BASE_URL ?>/timesheets/showMyList" method="post" id="form" name="form">

            <div class="pull-right">
                <div class="btn-group viewDropDown">
                    <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$tpl->__("links.list_view") ?> <?=$tpl->__("links.view") ?></button>
                    <ul class="dropdown-menu">
                        <li><a href="<?=BASE_URL?>/timesheets/showMy" ><?=$tpl->__("links.week_view") ?></a></li>
                        <li><a href="<?=BASE_URL?>/timesheets/showMyList" class="active"><?=$tpl->__("links.list_view") ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="pull-right" style="margin-right:3px;">
                <div id="tableButtons" style="display:inline-block"></div>
                <a onclick="jQuery('.headtitle').toggle();" class="btn btn-default "><?=$tpl->__("links.filter") ?> (1)</a>
            </div>

            <div class="clearfix"></div>

            <div class="headtitle filterBar" style="display:none;">

                <div class="filterBoxLeft">
                    <label for="dateFrom"><?php echo $tpl->__('label.date_from'); ?> <?php echo $tpl->__('label.date_to'); ?></label>
                    <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom"
                           value="<?php echo $tpl->getFormattedDateString($tpl->get('dateFrom')); ?>" style="margin-bottom:10px; width:90px; float:left; margin-right:10px"/>
                    <input type="text" id="dateTo" class="dateTo" name="dateTo"
                           value="<?php echo $tpl->getFormattedDateString($tpl->get('dateTo')); ?>" style="margin-bottom:10px; width:90px" />
                </div>
                <div class="filterBoxLeft">
                    <label for="kind"><?php echo $tpl->__("label.type")?></label>
                    <select id="kind" name="kind" onchange="submit();">
                        <option value="all"><?php echo $tpl->__("label.all_types"); ?></option>
                        <?php foreach ($tpl->get('kind') as $key => $row) {
                            echo'<option value="' . $key . '"';
                            if ($key == $tpl->get('actKind')) {
                                echo ' selected="selected"';
                            }
                            echo'>' . $tpl->__($row) . '</option>';
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
                        <th><?php echo $tpl->__("label.type")?></th>
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
                    $sum = $sum + $row['hours'];?>
                    <tr>
                        <td data-order="<?=$tpl->e($row['id']); ?>"> <a href="<?=BASE_URL?>/timesheets/editTime/<?=$row['id']?>" class="editTimeModal">#<?=$row['id'] . " - " . $tpl->__('label.edit'); ?> </a></td>
                        <td data-order="<?php echo $tpl->getFormattedDateString($row['workDate']); ?>">
                                            <?php echo $tpl->getFormattedDateString($row['workDate']); ?>
                        </td>
                        <td data-order="<?php $tpl->e($row['hours']); ?>"><?php $tpl->e($row['hours'] ?: 0); ?></td>
                        <td data-order="<?php $tpl->e($row['planHours']); ?>"><?php $tpl->e($row['planHours'] ?: 0); ?></td>
                                        <?php $diff = ($row['planHours'] ?: 0) - ($row['hours'] ?: 0); ?>
                        <td data-order="<?=$diff; ?>"><?php echo $diff; ?></td>
                        <td data-order="<?=$tpl->e($row['headline']); ?>"><a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php $tpl->e($row['headline']); ?></a></td>

                        <td data-order="<?=$tpl->e($row['name']); ?>"><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php $tpl->e($row['name']); ?></a></td>
                        <td><?php sprintf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?></td>
                        <td><?php echo $tpl->__($tpl->get('kind')[$row['kind']]); ?></td>
                        <td><?php $tpl->e($row['description']); ?></td>
                        <td data-order="<?php if ($row['invoicedEmpl'] == '1') {
                            echo $tpl->getFormattedDateString($row['invoicedEmplDate']);
                                        }?>"><?php if ($row['invoicedEmpl'] == '1') {
    ?> <?php echo $tpl->getFormattedDateString($row['invoicedEmplDate']); ?>
                                        <?php } else {
                                            echo $tpl->__("label.pending");
                                        } ?></td>
                        <td data-order="<?php if ($row['invoicedComp'] == '1') {
                            echo $tpl->getFormattedDateString($row['invoicedCompDate']);
                                        }?>">
                            <?php if ($row['invoicedComp'] == '1') {
                                ?> <?php echo $tpl->getFormattedDateString($row['invoicedCompDate']); ?>
                            <?php } else {
                                echo $tpl->__("label.pending");
                            } ?></td>
                        <td data-order="<?php if ($row['paid'] == '1') {
                            echo $tpl->getFormattedDateString($row['paidDate']);
                                        }?>">
                            <?php if ($row['paid'] == '1') {
                                ?> <?php echo $tpl->getFormattedDateString($row['paidDate']); ?>
                            <?php } else {
                                echo $tpl->__("label.pending");
                            } ?></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="1"><strong><?php echo $tpl->__("label.total_hours")?></strong></td>
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
        jQuery(".dateFrom, .dateTo").datepicker({
            numberOfMonths: 1,
            dateFormat:  leantime.i18n.__("language.jsdateformat"),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            currentText: leantime.i18n.__("language.currentText"),
            closeText: leantime.i18n.__("language.closeText"),
            buttonText: leantime.i18n.__("language.buttonText"),
            isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
            nextText: leantime.i18n.__("language.nextText"),
            prevText: leantime.i18n.__("language.prevText"),
            weekHeader: leantime.i18n.__("language.weekHeader"),
            firstDay: leantime.i18n.__("language.firstDayOfWeek"),
        });
    });

</script>
