<script type="text/javascript">
    jQuery(document).ready(function(){
        // dynamic table
        jQuery('#dyntable3').dataTable( {
            "bScrollInfinite": true,
            "bScrollCollapse": true,
            "sScrollY": "200px"
        });
        
    });
</script>

<table id="" class="table table-bordered">
	<colgroup>
        <col class="con1" />
      	<col class="con0"/>
	</colgroup>
	<thead>
		<tr>
			<th class='head1'><?php echo $language->lang_echo('PROJECT') ?></th>
			<th class='head0'><?php echo $language->lang_echo('PROGRESS') ?></th>
		</tr>
	</thead>
	<tbody>
 	 <?php foreach($this->get('myProjects') as $project): ?>
		<tr>
			<td><?php echo $this->displayLink('projects.showProject', $project['name'], array('id' => $project['id'])) ?></td>
			<td>
				<div class='progress progress-primary'>
					<div style='width: <?php echo round($project['projectPercentage']) ?>%' class='bar'>&nbsp;</div>
				</div>
			</td>
		</tr>
	 <?php endforeach; ?>
	</tbody>
</table>