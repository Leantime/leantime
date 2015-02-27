<script type='text/javascript'>

		// BAR GRAPH 
		var d2 = [];
		
		<?php $count = 0; ?>
		<?php foreach($this->get('closedTicketsPerWeek') as $week): ?>
			d2.push([<?php echo $count ?>, <?php echo $week ?>]);	
			<?php $count++ ?>
		<?php endforeach; ?>
			
		var stack = 0, bars = true, lines = false, steps = false;
		jQuery(document).ready(function(){
			jQuery.plot(jQuery("#bargraph2"), [ d2 ], {
				series: {
					stack: stack,
					lines: { show: lines, fill: true, steps: steps },
					bars: { show: bars, barWidth: 0.6 }
				},
				grid: { hoverable: true, clickable: true, borderColor: '#666', borderWidth: 2, labelMargin: 10 },
				colors: ["#666"]
			});
		});	
				
</script>
<?php

?>

<div class='statistics'>
	
	<p>
		<!--<strong>Average response time:</strong>
		<span><?php echo $this->get('avgResponseTime') ?></span>
		<br/>-->
		
		<h4><?php echo $language->lang_echo('CLOSED_TICKETS_PER_WEEK') ?></h4><br/>
		<div id="bargraph2" style="height:300px"></div>
		
		<strong>Hours spent per ticket:</strong>
		<span><?php echo $this->get('hoursPerTicket') ?></span>
		<br/>
		
		<strong>Hours bug fixing:</strong>
		<span><?php echo $this->get('hoursBugFixing') ?></span>
	
	</p>
	
</div>
