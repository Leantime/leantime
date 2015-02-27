<script type='text/javascript'>
		// BAR GRAPH 
		var d2 = [];
		d2.push([1, <?php echo $this->get('actualMoneyPerLead') ?>]);
		d2.push([2, <?php echo $this->get('estimatedMoneyPerLead') ?>]);
			
		var stack = 0, bars = true, lines = false, steps = false;
		jQuery(document).ready(function(){
			jQuery.plot(jQuery("#bargraph"), [ d2 ], {
				series: {
					stack: stack,
					lines: { show: lines, fill: true, steps: steps },
					bars: { show: bars, barWidth: 0.6 }
				},
				grid: { hoverable: true, clickable: true, borderColor: '#666', borderWidth: 2, labelMargin: 10 },
				colors: ["#666"]
			});
		});	


		/**PIE CHART IN MAIN PAGE WHERE LABELS ARE INSIDE THE PIE CHART */
		var data = [];
		var series = <?php echo count($this->get('refLabels')) ?>;
		<?php $refLabels = $this->get('refLabels'); $count = 0; ?>
		<?php foreach($this->get('references') as $key => $ref): ?>
		
		 data[<?php echo $count ?>] = { label: "<?php echo $refLabels[$key] ?>", data: <?php echo $ref ?> };
		 <?php $count++; ?>			
		
		<?php endforeach; ?>
		
		console.log(data);
		
		jQuery(document).ready(function() {
			jQuery.plot(jQuery("#piechart"), data, {
					colors: ['#680fb3','#9ab30f','#b32e0f','#0f6fb3','#b30fa6'],		   
					series: {
						pie: { show: true }
					}
			});		
		});
</script>
<style type='text/css'>
	.widgetright { float: left !important; width: 32% !important; margin-left: 1% !important; }	
</style>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        <h1><?php echo $language->lang_echo('STATISTICS'); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
	<div class="maincontentinner">	
		<div class='widgetbox widgetright'>
			<h4 class='widgettitle'><?php echo $language->lang_echo('AVG_MONEY_PER_LEAD') ?> <a class="close">x</a></h4>
			<div class="widgetcontent">
				<div class='avgMoneyPerLead'>
					<table style='width: 100%;'>
						<tr>
							<td class='center'><?php echo $language->lang_echo('ESTIMATED') ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td class='center'><?php echo $language->lang_echo('ACTUAL') ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						</tr>
					</table>
					<div id="bargraph" style="height:300px;"></div>
					<!--<br />
					<strong style='margin-left: 15px;'><?php echo $language->lang_echo('ESTIMATED') ?>: </strong><span class=''><?php echo $this->get('actualMoneyPerLead') ?></span><br/>
					<strong style='margin-left: 15px;'><?php echo $language->lang_echo('ACTUAL') ?>: </strong><span class=''><?php echo $this->get('estimatedMoneyPerLead') ?></span>-->	
				</div>
			</div>
		</div>
		<div class='widgetbox widgetright'>
			<h4 class='widgettitle'><?php echo $language->lang_echo('TOP_REFERENCE') ?> <a class="close">x</a></h4>
			<div class="widgetcontent">		
				<div class='topLeadReferrer'>
					<p><strong class=''><?php echo $this->get('topReferences') ?></strong>	</p>
					<div id="piechart" style="height:300px;"></div>
				</div>
			</div>
		</div>
		<div class='widgetbox widgetright'>
			<h4 class='widgettitle'><?php echo $language->lang_echo('STATISTICS') ?> <a class="close">x</a></h4>
			<div class="widgetcontent">			
				<div class='avgLeadPerMinute'>
					<strong><?php echo $language->lang_echo('AVG_LEAD_PER_MONTH') ?>:</strong>
					<span class=''><?php echo $this->get('avgLeadPerMonth') ?></span>
				</div>
				
				<div class='conversionRatio'>
					<strong><?php echo $language->lang_echo('CONVERSION_RATIO') ?>:</strong>
					<span class=''><?php echo $this->get('conversionRatio') ?></span>	
				</div>
				
				<div class='avgColdLeadAge'>
					<strong><?php echo $language->lang_echo('AVG_AGE_OF_COLD_LEAD') ?>:</strong>
					<span class=''><?php echo $this->get('avgColdLeadAge') ?></span>		
				</div>		
			</div>
		</div>	
		<div class='widgetbox clear'>
			<h4 class='widgettitle'><?php echo $language->lang_echo('NEW_LEADS') ?> <a class="close">x</a></h4>
			<div class="widgetcontent">			
				<div class='newLeads'>
					<table class='table table-bordered'>
						<colgroup>
							<col class='con0' />
							<col class='con1' />
						</colgroup>
						<thead>
							<tr>
								<th><?php echo $language->lang_echo('ID') ?></th>
								<th><?php echo $language->lang_echo('NAME') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->get('newLeads') as $lead): ?>
							<tr>
								<td><?php echo $lead['id'] ?></td>
								<td><?php echo $lead['name'] ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>


	</div>
</div>
				