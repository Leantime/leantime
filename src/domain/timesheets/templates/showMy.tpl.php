<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );


$helper = $this->get('helper');
?>
<script type="text/javascript">
jQuery(document).ready(function(){

 	var startDate;
    var endDate;

    var selectCurrentWeek = function () {
        window.setTimeout(function () {
            jQuery('.ui-weekpicker').find('.ui-datepicker-current-day a').addClass('ui-state-active').removeClass('ui-state-default');
        }, 1);
    }

    var setDates = function (input) {
        var $input = jQuery(input);
        var date = $input.datepicker('getDate');
        if (date !== null) {
            var firstDay = $input.datepicker( "option", "firstDay" );
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
    }



    jQuery('.week-picker').datepicker({
    	firstDay: 1,
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
        }
    });
    
    setDates('.week-picker');

    var $calendarTR = jQuery('.ui-weekpicker .ui-datepicker-calendar tr');
    $calendarTR.live('mousemove', function () {
        jQuery(this).find('td a').addClass('ui-state-hover');
    });
    $calendarTR.live('mouseleave', function () {
        jQuery(this).find('td a').removeClass('ui-state-hover');
    });

	jQuery("#startDate").datepicker("setDate", new Date(<?php echo $this->get("dateFrom")->format('Y, m-1, d'); ?>));
	jQuery("#endDate").datepicker("setDate", new Date(<?php echo $this->get("dateFrom")->add(new DateInterval('P6D'))->format('Y, m-1, d'); ?>));
	
	<?php $this->get("dateFrom")->sub(new DateInterval('P6D')); ?>
	
    jQuery(".project-select").chosen();
    jQuery(".ticket-select").chosen();
    
    jQuery(".project-select").change(function(){
    		jQuery(".ticket-select option").show();
    		jQuery("#ticketSelect .chzn-results li").show();
    		var selectedValue = jQuery(this).find("option:selected").val();
    		jQuery("#ticketSelect .chzn-results li").not(".project_"+selectedValue).hide();
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

    })
    
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
    			var currentValue = parseInt(jQuery(this).val());
    			rowSum = rowSum + currentValue;
    			
    			var currentClass = jQuery(this).parent().attr('class');
    			
    			switch(currentClass){
    				case "rowMo": colSumMo = colSumMo + currentValue; break;
    				case "rowTu": colSumTu = colSumTu + currentValue; break;
    				case "rowWe": colSumWe = colSumWe + currentValue; break;
    				case "rowTh": colSumTh = colSumTh + currentValue; break;
    				case "rowFr": colSumFr = colSumFr + currentValue; break;
    				case "rowSa": colSumSa = colSumSa + currentValue; break;
    				case "rowSu": colSumSu = colSumSu + currentValue; break;
    			}
    			
    		});
    		jQuery(this).find(".rowSum strong").text(rowSum);
    	});
    	
    	jQuery("#sumMo").text(colSumMo);
    	jQuery("#sumTu").text(colSumTu);
    	jQuery("#sumWe").text(colSumWe);
    	jQuery("#sumTh").text(colSumTh);
    	jQuery("#sumFr").text(colSumFr);
    	jQuery("#sumSa").text(colSumSa);
    	jQuery("#sumSu").text(colSumSu);
    	
    	var finalSum = colSumMo + colSumTu + colSumWe + colSumTh + colSumFr + colSumSa + colSumSu;
    	
    	jQuery("#finalSum").text(finalSum);
    	
    });

    <?php if(isset($_SESSION['userdata']['settings']["modals"]["mytimesheets"]) === false || $_SESSION['userdata']['settings']["modals"]["mytimesheets"] == 0){     ?>
    leantime.helperController.showHelperModal("mytimesheets");
    <?php
    //Only show once per session
    $_SESSION['userdata']['settings']["modals"]["mytimesheets"] = 1;
    } ?>
 });
    
</script>


<div class="pageheader">

            <div class="pageicon"><span class="iconfa-time"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('MY_TIMESHEETS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
                <?php
                echo $this->displayNotification();
                ?>

<form action="<?=BASE_URL ?>/timesheets/showMy" method="post" id="timesheetList">

<div class="headtitle" style="margin:0px; background: #eee;">
	<h4 class="widgettitle title-primary">My TimeSheet</h4>
	<div class="padding10">
		<span>Week from</span>
        <a href="javascript:void(0)" style="font-size:16px;" id="prevWeek"><i class="fa fa-chevron-left"></i></a>
		<input type="text" class="week-picker" name="startDate" id="startDate" placeholder="mm/dd/yyyy" value="" style="margin-top:5px;"/> <?php echo $lang["UNTIL"]; ?>
		<input type="text" class="week-picker" name="endDate" id="endDate" placeholder="mm/dd/yyyy" style="margin-top:6px;"/>
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

    $dateFromHeader = clone $this->get("dateFrom");
    $currentDate = $dateFromHeader->format('m/d/Y');
    ?>
	<tr>
		<th>Client/Project</th>
		<th>To-Do</th>
		<th>Type</th>
		<th>Mo <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>Tu <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>We <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>Th <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>Fr <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>Sa <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>Su <?php echo $currentDate; $currentDate = $dateFromHeader->add(new DateInterval('P1D'))->format('m/d/Y'); ?></th>
		<th>Total</th>
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
		
			
		foreach($this->get('allTimesheets') as $timeRow){
			
			$sumMon = $timeRow["hoursMonday"] + $sumMon;
			$sumTu = $timeRow["hoursTuesday"] + $sumTu;
			$sumWe = $timeRow["hoursWednesday"] + $sumWe;
			$sumTh = $timeRow["hoursThursday"] + $sumTh;
			$sumFr = $timeRow["hoursFriday"] + $sumFr;
			$sumSa = $timeRow["hoursSaturday"] + $sumSa;
			$sumSu = $timeRow["hoursSunday"] + $sumSu;

			$dateFrom = clone $this->get("dateFrom");
			
			$timesheetId = "new";
									
			$workDatesArray = explode(",", $timeRow["workDates"]);
			
			$rowSum = $timeRow["hoursMonday"] + $timeRow["hoursTuesday"] + $timeRow["hoursWednesday"] + $timeRow["hoursThursday"] + $timeRow["hoursFriday"] + $timeRow["hoursSaturday"] + $timeRow["hoursSunday"];
			
		?>
			
			<tr class="gradeA timesheetRow">
				<td width="14%"><?php echo $timeRow["name"]; ?></td>
				<td width="14%"><?php echo $timeRow["headline"]; ?></td>
				<td width="10%"><?php echo $lang[$timeRow['kind']]; ?></td>
				<?php $currentDate = $dateFrom->format('Y-m-d'); ?>
				<td width="7%" class="rowMo"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursMonday"]; ?>" /></td>
				<?php $currentDate = $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>
				<td width="7%" class="rowTu"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursTuesday"]; ?>" /></td>
				<?php $currentDate = $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>
				<td width="7%" class="rowWe"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursWednesday"]; ?>" /></td>
				<?php $currentDate = $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>
				<td width="7%" class="rowTh"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursThursday"]; ?>" /></td>
				<?php $currentDate = $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>
				<td width="7%" class="rowFr"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursFriday"]; ?>" /></td>
				<?php $currentDate = $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>
				<td width="7%" class="rowSa"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursSaturday"]; ?>" /></td>
				<?php $currentDate = $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>
				<td width="7%" class="rowSu"><input type="text" class="<?php echo $timeRow["workDates"]; ?> hourCell" name="<?php echo $timeRow["ticketId"];?>|<?php if(in_array($currentDate, $workDatesArray) == true) echo "existing"; else echo "new";?>|<?php echo $currentDate; ?>|<?php echo $timeRow["kind"];?>" value="<?php echo $timeRow["hoursSunday"]; ?>" /></td>
				<td width="7%" class="rowSum"><strong><?php echo $rowSum; ?></strong></td>
			</tr>
			
		<?php } ?>
		<?php
			$dateFrom = clone $this->get("dateFrom"); 
		?>
			<tr class="gradeA timesheetRow">
				<td width="14%">
					<div class="form-group">                    
                    	<select data-placeholder="Choose a Project..." style="" class="project-select" >
                         	<option value=""></option> 
                            <?php foreach($this->get('allProjects') as $projectRow){ ?>
                            	<?php echo"<option value=".$projectRow["id"].">".$this->escape($projectRow["clientName"])." / ".$this->escape($projectRow["name"])."</option>"; ?>
                            <?php }?>
                        </select>
                    </div>
				</td>
				<td width="14%">
					<div class="form-group" id="ticketSelect">                    
                    	<select data-placeholder="Choose a To-Do..." style="" class="ticket-select" name="ticketId">
                         	<option value=""></option> 
                            <?php foreach($this->get('allTickets') as $ticketRow){ ?>
                            	<?php echo"<option value=".$ticketRow["id"]." class='project_".$ticketRow["projectId"]."'>".$this->escape($ticketRow["headline"])."</option>"; ?>
                            <?php }?>
                        </select>
                    </div>
				</td>
				<td width="14%">
					<select class="kind-select" name="kindId">
                            <?php foreach($this->get('kind') as $kindRow){ ?>
                            	<?php echo"<option value=".$kindRow.">".$lang[$kindRow]."</option>"; ?>
                            <?php }?>
                        </select>
				</td>
				<td width="7%" class="rowMo"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowTu"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowWe"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowTh"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowFr"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowSa"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowSu"><input type="text" class="hourCell" name="new|new|<?php echo $dateFrom->add(new DateInterval('P1D'))->format('Y-m-d'); ?>|GENERAL_BILLABLE" value="0" /></td>
				<td width="7%" class="rowSum"><strong>0</strong></td>
			</tr>
	</tbody>
		
	<tfoot>
		<tr style="font-weight:bold;">
			<td colspan="3">Total:</td>
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

<?php /*

		
<table class='table table-bordered' cellpadding="0" cellspacing="0" border="0" class="table table-bordered" id="dyntable2">
		<colgroup>
      	  <col class="con0"/>
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
          <col class="con1" />
	</colgroup>
	<thead>
		<tr>
			<th><?php echo $lang['ID']; ?></th>
			<th><?php echo $lang['DATE']; ?></th>
			<th><?php echo $lang['HOURS']; ?></th>
			<th><?php echo $lang['BILLABLE_HOURS'] ?></th>
			<th><?php echo $lang['PLANHOURS']; ?></th>
			<th><?php echo $lang['DIFFERENCE_HOURS']; ?></th>
			<th><?php echo $lang['TICKET']; ?></th>
			<th><?php echo $lang['PROJECT']; ?></th>
			<th><?php echo $lang['KIND']; ?></th>
			<th><?php echo $lang['DESCRIPTION']; ?></th>
			
		</tr>
		
	</thead>
	<tbody>

	<?php
	$sum = 0;
	$billableSum = 0;
	foreach($this->get('allTimesheets') as $row) {
		$sum = $sum + $row['hours'];?>
		<tr>
			<td>
				<a href="<?=BASE_URL ?>/timesheets/editTime/<?php echo $row['id']; ?>">#<?php echo $row['id']; ?></a>
			</td>
			<td><?php echo $helper->timestamp2date($row['workDate'], 2); ?></td>
			<td><?php echo $row['hours']; ?></td>
			<td>
			<?php 
				if ($row['kind'] != 'GENERAL_NOT_BILLABLE' && $row['kind'] != 'BUGFIXING_NOT_BILLABLE') {
					echo $row['hours'];
					$billableSum += $row['hours'];
				}
			?>
			</td>
			<td><?php echo $row['planHours']; ?></td>
			<?php $diff = $row['planHours']-$row['hours']; ?>
			<td <?php if($diff<0)echo'class="new" ';?>><?php echo $diff; ?></td>
			<td><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $lang[$row['kind']]; ?></td>
			<td><?php echo $row['description']; ?></td>
			
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="12"><?php echo $lang['NO_RESULTS']; ?></td>
		</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><strong><?php echo $lang['ALL_HOURS']; ?>:</strong></td>
			<td colspan="1"><strong><?php echo $sum; ?></strong></td>
			<td colspan="7"><strong><?php echo $billableSum; ?></strong></td>
			
			</td></td>
		</tr>
	</tfoot>
</table>

</div>*/?>
</form>

					</div>
				</div>
