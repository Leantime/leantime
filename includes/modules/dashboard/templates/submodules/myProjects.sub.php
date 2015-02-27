
<table id='dyntable5' class='table table-bordered'>
	<colgroup>
        <col class="con1" />
      	<col class="con0"/>
	</colgroup>
	<thead>
		<tr>
			<th class='head0'><?php echo $language->lang_echo('ID') ?></th>
			<th class='head1'><?php echo $language->lang_echo('NAME') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->get('myProjects') as $project): ?>
			<tr>
				<td>
					<?php echo $this->displayLink('projects.showProject', $project['id'], array('id' =>$project['id'])); ?>
				</td>
				<td>
					<?php echo $this->displayLink('projects.showProject', $project['name'], array('id' =>$project['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
