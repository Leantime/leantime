<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$helper = $this->get('helper');
?>
<script type="text/javascript">
	
	jQuery(document).ready(function(){ 
		
	    jQuery("#checkAllEmpl").change(function(){	
	    	jQuery(".invoicedEmpl").prop('checked', jQuery(this).prop("checked"));
	    	if(jQuery(this).prop("checked") == true){
	    		jQuery(".invoicedEmpl").attr("checked", "checked");
	    		jQuery(".invoicedEmpl").parent().addClass("checked");
	    	}else{
	    		jQuery(".invoicedEmpl").removeAttr("checked");
	    		jQuery(".invoicedEmpl").parent().removeClass("checked");
	    	}
	    	
	    });
	    
	    jQuery("#checkAllComp").change(function(){
	    	jQuery(".invoicedComp").prop('checked', jQuery(this).prop("checked"));
	    	if(jQuery(this).prop("checked") == true){
	    		jQuery(".invoicedComp").attr("checked", "checked");
	    		jQuery(".invoicedComp").parent().addClass("checked");
	    	}else{
	    		jQuery(".invoicedComp").removeAttr("checked");
	    		jQuery(".invoicedComp").parent().removeClass("checked");
	    	}
	    });

	    leantime.timesheetsController.initTimesheetsTable();
	});		
        
    
</script>

<div class="pageheader">


    <div class="pageicon"><span class="iconfa-time"></span></div>
            <div class="pagetitle">
                <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
                <h1><?php echo $this->__("headline.project_timesheets") ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<form action="<?=BASE_URL ?>/timesheets/showAll" method="post" id="form" name="form">


<a onclick="jQuery('.headtitle').toggle();" class="btn btn-default"><?=$this->__("links.filter") ?></a>
    <div id="tableButtons" style="float:right;"></div>

<div class="headtitle" style="margin:0px; background: #eee;">

	<table cellpadding="10" cellspacing="0" width="90%" style=" border: 1px solid #ccc; margin-top:0px;" class="table dataTable filterTable">

		<tr>
			<td><label for="dateFrom"><?php echo $this->__('label.date_from'); ?></label>
                <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom"
				value="<?php echo $this->get('dateFrom'); ?>" size="7" style="margin-bottom:10px"/></td>
			<td><label for="dateTo"><?php echo $this->__('label.date_to'); ?></label>
                <input type="text" id="dateTo" class="dateTo" name="dateTo"
				value="<?php echo $this->get('dateTo'); ?>" size="7" style="margin-bottom:10px" /></td>
			<td>
			<label for="userId"><?php echo $this->__("label.employee"); ?></label>
			<select name="userId" id="userId" onchange="submit();">
				<option value="all"><?php echo $this->__("label.all_employees"); ?></option>
	
				<?php foreach($this->get('employees') as $row) {
					echo'<option value="'.$row['id'].'"';
					if($row['id'] == $this->get('employeeFilter')) echo' selected="selected" ';
					echo'>'.$row['lastname'].', '.$row['firstname'].'</option>';
				}
	
				?>
			</select>
            </td>
            <td>
			<label for="kind"><?php echo $this->__("label.type")?></label>
			<select id="kind" name="kind" onchange="submit();">
				<option value="all"><?php echo $this->__("label.all_types"); ?></option>
				<?php foreach($this->get('kind') as $key => $row){
					echo'<option value="'.$key.'"';
					if($key == $this->get('actKind')) echo ' selected="selected"';
					echo'>'.$this->__($row).'</option>';
	
				}
				?>
	
			</select> </td>
			<td>

			<input type="checkbox" value="on" name="invEmpl" id="invEmpl" onclick="submit();" 
				<?php 
				if($this->get('invEmpl') == '1') echo ' checked="checked"';
				?>
			/><label for="invEmpl"><?php echo $this->__("label.invoiced"); ?></label></td>
            <td>

			<input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();" 
				<?php 
				if($this->get('invComp') == '1') echo ' checked="checked"';
				?>
			/><label for="invEmpl"><?php echo $this->__("label.invoiced_comp"); ?></label>
			</td>
			<td>
				<input type="submit" value="<?php echo $this->__('buttons.search')?>" class="reload" />
			</td>
		</tr>

</table>

</div>
<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered display" id="allTimesheetsTable">
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
      	  <col class="con0"/>
	</colgroup>
	<thead>
		<tr>
			<th><?php echo $this->__('label.date'); ?></th>
			<th><?php echo $this->__('label.hours'); ?></th>
			<th><?php echo $this->__('label.plan_hours'); ?></th>
			<th><?php echo $this->__('label.difference'); ?></th>
			<th><?php echo $this->__('label.ticket'); ?></th>
			<th><?php echo $this->__('label.project'); ?></th>
			<th><?php echo $this->__('label.employee'); ?></th>
			<th><?php echo $this->__("label.type")?></th>
			<th><?php echo $this->__('label.description'); ?></th>
			<th><?php echo $this->__('label.invoiced'); ?></th>
			<th><?php echo $this->__('label.invoiced_comp'); ?></th>
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
                <?php echo date($this->__("language.dateformat"), strtotime($row['workDate'])); ?>
            </td>
			<td><?php $this->e($row['hours']); ?></td>
			<td><?php $this->e($row['planHours']); ?></td>
			<?php $diff = $row['planHours']-$row['hours']; ?>
			<td><?php echo $diff; ?></td>
			<td data-order="<?=$this->e($row['headline']); ?>"><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php $this->e($row['headline']); ?></a></td>

			<td data-order="<?=$this->e($row['name']); ?>"><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php $this->e($row['name']); ?></a></td>
			<td><?php $this->e($row['firstname']); ?>, <?php $this->e($row['lastname']); ?></td>
			<td><?php echo $this->__($this->get('kind')[$row['kind']]); ?></td>
			<td><?php $this->e($row['description']); ?></td>
			<td data-order="<?php if($row['invoicedEmpl'] == '1'){ echo "true"; }?>"><?php if($row['invoicedEmpl'] == '1'){?> <?php echo date($this->__("language.dateformat"), strtotime($row['invoicedEmplDate'])); ?>
			<?php }else{ ?> <input type="checkbox" name="invoicedEmpl[]" class="invoicedEmpl"
				value="<?php echo $row['id']; ?>" /> <?php } ?></td>
			<td data-order="<?php if($row['invoicedComp'] == '1'){ echo "true"; }?>"><?php if($row['invoicedComp'] == '1'){?> <?php echo date($this->__("language.dateformat"), strtotime($row['invoicedCompDate'])); ?>
			<?php }else{ ?> <input type="checkbox" name="invoicedComp[]" class="invoicedComp"
				value="<?php echo $row['id']; ?>" /> <?php } ?></td>
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="13"><?php echo $this->__("label.no_results"); ?></td>
		</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="1"><strong><?php echo $this->__("label.total_hours")?></strong></td>
			<td colspan="7"><strong><?php echo $sum; ?></strong></td>

			<td>
				<input type="submit" class="button" value="<?php echo $this->__('buttons.save'); ?>" name="saveInvoice" />
			</td>
			<td><input type="checkbox" id="checkAllEmpl" /><?php echo $this->__('label.select_all')?></td>
			<td><input type="checkbox"  id="checkAllComp" /><?php echo $this->__('label.select_all')?></td>
		</tr>
	</tfoot>
</table>

</form>


			</div>
		</div>