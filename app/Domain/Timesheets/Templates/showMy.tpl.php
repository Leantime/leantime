<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>
<script type="text/javascript">

jQuery(document).ready(function(){
    var startDate;
    var endDate;
    var selectCurrentWeek = function () {
        window.setTimeout(function () {
            jQuery('.ui-weekpicker').find('.ui-datepicker-current-day a').addClass('ui-state-active').removeClass('ui-state-default');
        }, 1);
    };

    var setDates = function (input) {
        var $input = jQuery(input);
        var date = $input.datepicker('getDate');

        if (date !== null) {
            // Timesheet table currently does not handle first day of week. We are setting it to Monday no matter what
            // var firstDay = $input.datepicker( "option", "firstDay" );
            var firstDay = 1
            var dayAdjustment = date.getDay() - firstDay;
            if (dayAdjustment < 0) {
                dayAdjustment += 7;
            }
            startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - dayAdjustment);
            endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - dayAdjustment + 6);

            var inst = $input.data('datepicker');
            var dateFormat = inst.settings.dateFormat || jQuery.datepicker._defaults.dateFormat;

            jQuery('#startDate').val(jQuery.datepicker.formatDate(dateFormat, startDate, inst.settings));
            jQuery('#endDate').val(jQuery.datepicker.formatDate(dateFormat, endDate, inst.settings));
        }
    };

    jQuery('.week-picker').datepicker({
        dateFormat:  leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
        dayNames: leantime.i18n.__("language.dayNames").split(","),
        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
        monthNames: leantime.i18n.__("language.monthNames").split(","),
        currentText: leantime.i18n.__("language.currentText"),
        closeText: leantime.i18n.__("language.closeText"),
        buttonText: leantime.i18n.__("language.buttonText"),
        isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
        nextText: leantime.i18n.__("language.nextText"),
        prevText: leantime.i18n.__("language.prevText"),
        weekHeader: leantime.i18n.__("language.weekHeader"),
        firstDay: 1, //Hard coding to monday for this specific instance.

        beforeShow: function () {
            jQuery('#ui-datepicker-div').addClass('ui-weekpicker');
            selectCurrentWeek();
        },
        onClose: function () {
            jQuery('#ui-datepicker-div').removeClass('ui-weekpicker');
        },
        showOtherMonths: true,
        selectOtherMonths: true,
        onSelect: function (dateText, inst) {

            setDates(this);
            selectCurrentWeek();
            jQuery(this).change();
            jQuery("#timesheetList").submit();
        },
        beforeShowDay: function (date) {
            var cssClass = '';
            if (date >= startDate && date <= endDate)
                cssClass = 'ui-datepicker-current-day';
            return [true, cssClass];
        },
        onChangeMonthYear: function (year, month, inst) {
            selectCurrentWeek();
        },
    });


    var $calendarTR = jQuery('.ui-weekpicker .ui-datepicker-calendar tr');
    $calendarTR.on('mousemove', function () {
        jQuery(this).find('td a').addClass('ui-state-hover');
    });
    $calendarTR.on('mouseleave', function () {
        jQuery(this).find('td a').removeClass('ui-state-hover');
    });

    jQuery(".project-select").chosen();
    jQuery(".ticket-select").chosen();
    jQuery(".project-select").change(function(){
            jQuery(".ticket-select").removeAttr("selected");
            jQuery(".ticket-select").val("");
            jQuery(".ticket-select").trigger("liszt:updated");

            jQuery(".ticket-select option").show();
            jQuery("#ticketSelect .chosen-results li").show();
            var selectedValue = jQuery(this).find("option:selected").val();
            jQuery(".ticket-select option").not(".project_"+selectedValue).hide();
            jQuery("#ticketSelect .chosen-results li").not(".project_"+selectedValue).hide();
            jQuery(".ticket-select").chosen("destroy").chosen();
    });

    jQuery(".ticket-select").change(function() {
        var selectedValue = jQuery(this).find("option:selected").attr("data-value");
        jQuery(".project-select option[value="+selectedValue+"]").attr("selected", "selected");
        jQuery(".project-select").trigger("liszt:updated");
        jQuery(".ticket-select").chosen("destroy").chosen();
    });

    jQuery("#nextWeek").click(function() {
        var date = jQuery("#endDate").datepicker('getDate');
        var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 7);
        var startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

        var inst = jQuery("#endDate").data('datepicker');
        var dateFormat = inst.settings.dateFormat || jQuery.datepicker._defaults.dateFormat;
        jQuery('#startDate').val(jQuery.datepicker.formatDate(dateFormat, startDate, inst.settings));
        jQuery('#endDate').val(jQuery.datepicker.formatDate(dateFormat, endDate, inst.settings));
        jQuery("#timesheetList").submit();
    });

    jQuery("#prevWeek").click(function() {

        var date = jQuery("#startDate").datepicker('getDate');
        var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
        var startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 7);

        var inst = jQuery("#startDate").data('datepicker');
        var dateFormat = inst.settings.dateFormat || jQuery.datepicker._defaults.dateFormat;
        jQuery('#startDate').val(jQuery.datepicker.formatDate(dateFormat, startDate, inst.settings));
        jQuery('#endDate').val(jQuery.datepicker.formatDate(dateFormat, endDate, inst.settings));
       jQuery("#timesheetList").submit();

    });

    jQuery(".timesheetTable input").change(function(){
        //Row Sum
        colSumMo = 0;
        colSumTu = 0;
        colSumWe = 0;
        colSumTh = 0;
        colSumFr = 0;
        colSumSa = 0;
        colSumSu = 0;

        jQuery(".timesheetRow").each(function(i){
            var rowSum = 0;

            jQuery(this).find("input.hourCell").each(function(){
                var currentValue = parseFloat(jQuery(this).val());
                rowSum = Math.round((rowSum + currentValue)*100)/100;

                var currentClass = jQuery(this).parent().attr('class');

                if(currentClass.indexOf("rowMo") > -1){ colSumMo = colSumMo + currentValue; }
                if(currentClass.indexOf("rowTu") > -1){ colSumTu = colSumTu + currentValue; }
                if(currentClass.indexOf("rowWe") > -1){ colSumWe = colSumWe + currentValue;  }
                if(currentClass.indexOf("rowTh") > -1){ colSumTh = colSumTh + currentValue; }
                if(currentClass.indexOf("rowFr") > -1){ colSumFr = colSumFr + currentValue;  }
                if(currentClass.indexOf("rowSa") > -1){ colSumSa = colSumSa + currentValue;  }
                if(currentClass.indexOf("rowSu") > -1){ colSumSu = colSumSu + currentValue;  }
            });

            jQuery(this).find(".rowSum strong").text(rowSum);
        });

        jQuery("#sumMo").text(colSumMo.toFixed(2));
        jQuery("#sumTu").text(colSumTu.toFixed(2));
        jQuery("#sumWe").text(colSumWe.toFixed(2));
        jQuery("#sumTh").text(colSumTh.toFixed(2));
        jQuery("#sumFr").text(colSumFr.toFixed(2));
        jQuery("#sumSa").text(colSumSa.toFixed(2));
        jQuery("#sumSu").text(colSumSu.toFixed(2));

        var finalSum = colSumMo + colSumTu + colSumWe + colSumTh + colSumFr + colSumSa + colSumSu;
        var roundedSum = Math.round((finalSum)*100)/100;

        jQuery("#finalSum").text(roundedSum);
    });
 });
</script>

<div class="pageheader">

    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('headline.overview'); ?></h5>
        <h1><?php echo $tpl->__('headline.my_timesheets'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <?php
        echo $tpl->displayNotification();
        ?>



        <form action="<?=BASE_URL ?>/timesheets/showMy" method="post" id="timesheetList">
            <div class="btn-group viewDropDown pull-right">
                <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$tpl->__("links.week_view") ?> <?=$tpl->__("links.view") ?></button>
                <ul class="dropdown-menu">
                    <li><a href="<?=BASE_URL?>/timesheets/showMy" class="active"><?=$tpl->__("links.week_view") ?></a></li>
                    <li><a href="<?=BASE_URL?>/timesheets/showMyList" ><?=$tpl->__("links.list_view") ?></a></li>
                </ul>
            </div>
            <div class="pull-left" style="padding-left:5px; margin-top:-3px;">

                <div class="padding-top-sm">
                    <span><?php echo $tpl->__('label.week_from')?></span>
                    <a href="javascript:void(0)" style="font-size:16px;" id="prevWeek"><i class="fa fa-chevron-left"></i></a>
                    <input type="text" class="week-picker" name="startDate" autocomplete="off" id="startDate" placeholder="<?php echo $tpl->__('language.dateformat')?>" value="<?=format($tpl->get("dateFrom"))->date() ?>" style="margin-top:5px;"/> <?php echo $tpl->__('label.until'); ?>
                    <input type="text" class="week-picker" name="endDate" autocomplete="off" id="endDate" placeholder="<?php echo $tpl->__('language.dateformat')?>" value="<?=format($tpl->get("dateFrom")->add(new DateInterval('P6D')))->date() ?>" style="margin-top:6px;"/>
                    <a href="javascript:void(0)" style="font-size:16px;" id="nextWeek"><i class="fa fa-chevron-right"></i></a>
                    <input type="hidden" name="search" value="1" />

                    <?php
                        //Return date to beginning
                        $tpl->get("dateFrom")->sub(new DateInterval('P6D'));
                    ?>

                </div>

            </div>
            <table cellpadding="0" width="100%" class="table table-bordered display timesheetTable" id="dyntableX">
                <colgroup>
                      <col class="con0" >
                      <col class="con1" >
                      <col class="con0" >
                      <col class="con1" >
                      <col class="con0" >
                      <col class="con1" >
                      <col class="con0" >
                      <col class="con1" >
                      <col class="con0" >
                      <col class="con1">
                      <col class="con0">

                </colgroup>
                <thead>
                <?php

                $dateFromHeader = clone $tpl->get("dateFrom");
                $currentDate = format($dateFromHeader)->date();
                $days = explode(',', $tpl->__('language.dayNamesShort'));
                $todayObject = new \DateTime('now', new \DateTimeZone("UTC"));
                $today = format($todayObject)->date();

                ?>
                <tr>
                    <th><?php echo $tpl->__('label.client_product')?></th>
                    <th><?php echo $tpl->__('subtitles.todo')?></th>
                    <th><?php echo $tpl->__('label.type')?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[1]?><br /><?php echo $currentDate;
                    $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[2]?><br /><?php echo $currentDate;
                        $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[3]?><br /><?php echo $currentDate;
                        $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[4]?><br /><?php echo $currentDate;
                        $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[5]?><br /><?php echo $currentDate;
                        $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[6]?><br /><?php echo $currentDate;
                        $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $days[0]?><br /><?php echo $currentDate;
                        $currentDate = format($dateFromHeader->add(new DateInterval('P1D')))->date(); ?></th>
                    <th class="<?php if ($today == $currentDate) {
                        echo "active";
                               } ?>"><?php echo $tpl->__('label.total')?></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                        $sumMon = 0;
                        $sumTu = 0;
                        $sumWe = 0;
                        $sumTh = 0;
                        $sumFr = 0;
                        $sumSa = 0;
                        $sumSu = 0;

                    foreach ($tpl->get('allTimesheets') as $timeRow) {

                        $sumMon = $timeRow["hoursMonday"] + $sumMon;
                        $sumTu = $timeRow["hoursTuesday"] + $sumTu;
                        $sumWe = $timeRow["hoursWednesday"] + $sumWe;
                        $sumTh = $timeRow["hoursThursday"] + $sumTh;
                        $sumFr = $timeRow["hoursFriday"] + $sumFr;
                        $sumSa = $timeRow["hoursSaturday"] + $sumSa;
                        $sumSu = $timeRow["hoursSunday"] + $sumSu;

                        $dateFrom = clone $tpl->get("dateFrom");

                        $timesheetId = "new";

                        $workDatesArray = explode(",", $timeRow["workDates"]);
                        $workDatesArray = array_map(function($item){ return format($item)->date(); }, $workDatesArray);

                        $rowSum = $timeRow["hoursMonday"] + $timeRow["hoursTuesday"] + $timeRow["hoursWednesday"] + $timeRow["hoursThursday"] + $timeRow["hoursFriday"] + $timeRow["hoursSaturday"] + $timeRow["hoursSunday"];

                        ?>

                        <tr class="gradeA timesheetRow">
                            <td width="14%"><?php $tpl->e($timeRow["clientName"]); ?> // <?php $tpl->e($timeRow["name"]); ?></td>
                            <td width="14%"><?php $tpl->e($timeRow["headline"]); ?></td>
                            <td width="10%"><?php echo $tpl->__($tpl->get('kind')[$timeRow['kind']]); ?></td>
                            <?php $currentDate = format($dateFrom)->date(); ?>
                            <td width="7%" class="rowMo <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>" <?php if ($today == $currentDate) {
                                                        echo"active";
                                                        } ?>><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursMonday"]; ?>" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowTu <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursTuesday"]; ?>" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowWe <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursWednesday"]; ?>" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowTh <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursThursday"]; ?>" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowFr <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursFriday"]; ?>" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowSa <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursSaturday"]; ?>" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowSu <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if (
                                                            in_array(
                                                                $currentDate,
                                                                $workDatesArray
                                                            )
                                                        ) {
                                                            echo "existing";
                                                        } else {
                                                            echo "new";
                                                        }?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursSunday"]; ?>" /></td>
                            <td width="7%" class="rowSum <?php if ($today == $currentDate) {
                                echo"active";
                                                         } ?>"><strong><?php echo $rowSum; ?></strong></td>
                        </tr>

                    <?php } ?>
                    <?php
                        $dateFrom = clone $tpl->get("dateFrom");
                    ?>
                        <tr class="gradeA timesheetRow">
                            <td width="14%">
                                <div class="form-group">
                                    <select data-placeholder="<?php echo $tpl->__('input.placeholders.choose_project')?>" style="" class="project-select" >
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
                                    <select data-placeholder="<?php echo $tpl->__('input.placeholders.choose_todo')?>" style="" class="ticket-select" name="ticketId">
                                        <option value=""></option>
                                        <?php foreach ($tpl->get('allTickets') as $ticketRow) { ?>
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
                                        <?php }?>
                                    </select>
                                </div>
                            </td>
                            <td width="14%">
                                <select class="kind-select" name="kindId">
                                        <?php foreach ($tpl->get('kind') as $key => $kindRow) { ?>
                                            <?php echo"<option value=" . $key . ">" . $tpl->__($kindRow) . "</option>"; ?>
                                        <?php }?>
                                    </select>
                            </td>
                            <?php $currentDate = format($dateFrom)->date(); ?>
                            <td width="7%" class="rowMo <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowTu <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowWe <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowTh <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowFr <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowSa <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <?php $currentDate = format($dateFrom->add(new DateInterval('P1D')))->date(); ?>
                            <td width="7%" class="rowSu <?php if ($today == $currentDate) {
                                echo"active";
                                                        } ?>"><input type="text" class="hourCell" name="new|new|<?php  echo $currentDate ?>|GENERAL_BILLABLE" value="0" /></td>
                            <td width="7%" class="rowSum "><strong>0</strong></td>
                        </tr>
                </tbody>

                <tfoot>
                    <tr style="font-weight:bold;">
                        <td colspan="3"><?php echo $tpl->__('label.total')?></td>
                        <td id="sumMo"><?php echo $sumMon; ?></td>
                        <td id="sumTu"><?php echo $sumTu; ?></td>
                        <td id="sumWe"><?php echo $sumWe; ?></td>
                        <td id="sumTh"><?php echo $sumTh; ?></td>
                        <td id="sumFr"><?php echo $sumFr; ?></td>
                        <td id="sumSa"><?php echo $sumSa; ?></td>
                        <td id="sumSu"><?php echo $sumSu; ?></td>
                        <td id="finalSum"><?php echo ($sumMon + $sumTu + $sumWe + $sumTh + $sumFr + $sumSa + $sumSu); ?></td>
                    </tr>
                </tfoot>
            </table>
            <div class="right">
                <input type="submit" name="saveTimeSheet" value="Save"/>
            </div>

            <div class="clearall"></div>

        </form>

    </div>
</div>
