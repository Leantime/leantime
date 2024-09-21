<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}

/** @var \Carbon\Carbon $currentDate */
$dateFrom = $tpl->get("dateFrom");

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
        let colSum1 = 0;
        let colSum2 = 0;
        let colSum3 = 0;
        let colSum4 = 0;
        let colSum5 = 0;
        let colSum6 = 0;
        let colSum7 = 0;

        jQuery(".timesheetRow").each(function(i){
            var rowSum = 0;

            jQuery(this).find("input.hourCell").each(function(){
                var currentValue = parseFloat(jQuery(this).val());
                rowSum = Math.round((rowSum + currentValue)*100)/100;

                var currentClass = jQuery(this).parent().attr('class');

                if(currentClass.indexOf("rowday1") > -1){ colSum1 = colSum1 + currentValue; }
                if(currentClass.indexOf("rowday2") > -1){ colSum2 = colSum2 + currentValue; }
                if(currentClass.indexOf("rowday3") > -1){ colSum3 = colSum3 + currentValue; }
                if(currentClass.indexOf("rowday4") > -1){ colSum4 = colSum4 + currentValue; }
                if(currentClass.indexOf("rowday5") > -1){ colSum5 = colSum5 + currentValue; }
                if(currentClass.indexOf("rowday6") > -1){ colSum6 = colSum6 + currentValue; }
                if(currentClass.indexOf("rowday7") > -1){ colSum7 = colSum7 + currentValue; }
            });

            jQuery(this).find(".rowSum strong").text(rowSum);
        });

        jQuery("#day1").text(colSum1.toFixed(2));
        jQuery("#day2").text(colSum2.toFixed(2));
        jQuery("#day3").text(colSum3.toFixed(2));
        jQuery("#day4").text(colSum4.toFixed(2));
        jQuery("#day5").text(colSum5.toFixed(2));
        jQuery("#day6").text(colSum6.toFixed(2));
        jQuery("#day7").text(colSum7.toFixed(2));

        var finalSum = colSum1 + colSum2 + colSum3 + colSum4 + colSum5 + colSum6 + colSum7;
        var roundedSum = Math.round((finalSum)*100)/100;

        jQuery("#finalSum").text(roundedSum);
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

        <form action="<?php echo BASE_URL ?>/timesheets/showMy" method="post" id="timesheetList">
            <div class="btn-group viewDropDown pull-right">
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                    <?php echo $tpl->__("links.week_view") ?> <?=$tpl->__("links.view") ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo BASE_URL ?>/timesheets/showMy" class="active"><?php echo $tpl->__("links.week_view") ?></a></li>
                    <li><a href="<?php echo BASE_URL ?>/timesheets/showMyList" ><?php echo $tpl->__("links.list_view") ?></a></li>
                </ul>
            </div>
            <div class="pull-left" style="padding-left:5px; margin-top:-3px;">

                <div class="padding-top-sm">
                    <span><?php echo $tpl->__('label.week_from')?></span>
                    <a href="javascript:void(0)" style="font-size:16px;" id="prevWeek"><i class="fa fa-chevron-left"></i></a>
                    <input type="text" class="week-picker" name="startDate" autocomplete="off" id="startDate" placeholder="<?php echo $tpl->__('language.dateformat')?>" value="<?php echo $dateFrom->formatDateForUser() ?>" style="margin-top:5px;"/>
                    <?php echo $tpl->__('label.until'); ?>
                    <input type="text" class="week-picker" name="endDate" autocomplete="off" id="endDate" placeholder="<?php echo $tpl->__('language.dateformat')?>" value="<?php echo $dateFrom->addDays(6)->formatDateForUser() ?>" style="margin-top:6px;"/>
                    <a href="javascript:void(0)" style="font-size:16px;" id="nextWeek"><i class="fa fa-chevron-right"></i></a>
                    <input type="hidden" name="search" value="1" />
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

                    $days = explode(',', $tpl->__('language.dayNamesShort'));
                    // Make the first day of week monday, by shifting sunday to the back of the array.
                    $days[] = array_shift($days);
                ?>
                <tr>
                    <th><?php echo $tpl->__('label.client_product')?></th>
                    <th><?php echo $tpl->__('subtitles.todo')?></th>
                    <th><?php echo $tpl->__('label.type')?></th>
                    <?php
                    $i = 0;
                    foreach ($days as $day) { ?>
                        <th class="<?php if ($dateFrom->addDays($i)->setToUserTimezone()->isToday()) {
                            echo "active";
                                   } ?>
                    "><?php echo $day ?><br />
                            <?php

                                echo $dateFrom->addDays($i)->formatDateForUser();
                                $i++;
                            ?>
                        </th>
                    <?php } ?>
                    <th><?php echo $tpl->__('label.total')?></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                        $colSum = [
                            "day1" => 0,
                            "day2" => 0,
                            "day3" => 0,
                            "day4" => 0,
                            "day5" => 0,
                            "day6" => 0,
                            "day7" => 0,
                        ];
                        // @todo: move all this calculations into the service the timesheets class.
                        foreach ($tpl->get('allTimesheets') as $timeRow) {
                            $timesheetId = "new";
                            ?>
                            <tr class="gradeA timesheetRow">
                                <td width="14%"><?php $tpl->e($timeRow["clientName"]); ?> // <?php $tpl->e($timeRow["name"]); ?></td>
                                <td width="14%">
                                    <a href="#/tickets/showTicket/<?php echo $timeRow['ticketId']; ?>"><?php $tpl->e($timeRow['headline']); ?></a>
                                </td>
                                <td width="10%">
                                <?php echo $tpl->__($tpl->get('kind')[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? $tpl->get('kind')['GENERAL_BILLABLE']); ?>
                            <?php if ($timeRow['hasTimesheetOffset']) { ?>
                                    <i class="fa-solid fa-clock-rotate-left pull-right label-blue"
                                       data-tippy-content="This entry was likely created using a different timezone. Only existing entries can be updated in this timezone">
                                    </i>
                            <?php } ?>
                                </td>

                            <?php foreach (array_keys($timeRow) as $dayKey) {
                                if (str_starts_with($dayKey, "day")) {
                                    $colSum[$dayKey] = ($colSum[$dayKey] ?? 0) + $timeRow[$dayKey]["hours"]; ?>

                                        <td width="7%" class="row<?php
                                            echo $dayKey;
                                        if ($timeRow[$dayKey]["start"]->setToUserTimezone()->isToday()) {
                                            echo " active";
                                        }
                                        ?>">


                                            <?php
                                            $inputNameKey = $timeRow["ticketId"] . "|" . $timeRow["kind"] . "|" . ($timeRow[$dayKey]["actualWorkDate"] ? $timeRow[$dayKey]["actualWorkDate"]->formatDateForUser() : "false" ). "|" .  ($timeRow[$dayKey]["actualWorkDate"] ? $timeRow[$dayKey]["actualWorkDate"]->getTimestamp() : "false");
                                            ?>
                                            <input type="text"
                                                   class="hourCell"
                                                   <?php if(empty($timeRow[$dayKey]["actualWorkDate"])) echo "disabled='disabled'"; ?>
                                                   name="<?php echo $inputNameKey ?>"
                                                   value="<?php echo $timeRow[$dayKey]["hours"]; ?>"
                                                    <?php if(empty($timeRow[$dayKey]["actualWorkDate"])) { ?>
                                                        data-tippy-content="Cannot add time entry in previous timezone"
                                                    <?php } ?>
                                            />

                                            <?php if (!empty($timeRow[$dayKey]["description"])) {?>
                                                <i class="fa fa-circle-info" data-tippy-content="<?php echo $tpl->escape($timeRow[$dayKey]["description"]); ?>"></i>
                                            <?php } ?>
                                        </td>
                                    <?php
                                }
                            } ?>

                                <td width="7%" class="rowSum"><strong><?php echo $timeRow["rowSum"]; ?></strong></td>
                            </tr>
                        <?php } ?>

                        <!-- Row to add new time registration -->
                        <tr class="gradeA timesheetRow">
                            <td width="14%">
                                <div class="form-group" id="projectSelect">
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
                                        <?php foreach ($tpl->get('allTickets') as $ticketRow) {
                                            if(in_array($ticketRow['id'],$tpl->get('existingTicketIds'))) continue;
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

                            <?php
                            $i = 0;
                            foreach ($days as $day) {
                                ?>
                                <td width="7%" class="rowday<?php echo ($i + 1); ?><?php if ($dateFrom->addDays($i)->setToUserTimezone()->isToday()) {
                                    echo " active";
                                                            } ?>">
                                    <input type="text" class="hourCell" name="new|GENERAL_BILLABLE|<?php echo $dateFrom->addDays($i)->formatDateForUser() ?>|<?php echo $dateFrom->addDays($i)->getTimestamp() ?>" value="0" />
                                </td>
                                <?php
                                $i++;
                            } ?>
                        </tr>
                </tbody>

                <tfoot>
                    <tr style="font-weight:bold;">
                        <td colspan="3"><?php echo $tpl->__('label.total')?></td>
                        <?php
                        $totalHours = 0;
                        foreach ($colSum as $key => $col) {
                            $totalHours += $col;

                            ?>
                            <td id="<?php echo $key ?>"><?php echo $col; ?></td>
                        <?php } ?>
                        <td id="finalSum"><?php echo $totalHours; ?></td>
                    </tr>
                </tfoot>
            </table>
            <div class="right">
                <input type="submit" name="saveTimeSheet" value="Save" />
            </div>
            <div class="clearall"></div>
        </form>
    </div>
</div>
