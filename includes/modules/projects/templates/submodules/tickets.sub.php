	<?php echo $this->displayLink('tickets.newTicket', $language->lang_echo('NEW_TICKET'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>
	
	<table class='table table-bordered' id='dyntable2'>
		<colgroup>
		      	<col class="con0" />
		        <col class="con1" />
		      	<col class="con0" />
		        <col class="con1" />
		</colgroup>
		<thead>
			<tr>
				<th class='head0'><?php echo $language->lang_echo('ID') ?></th>
				<th class='head1'><?php echo $language->lang_echo('NAME') ?></th>
				<th class='head0'><?php echo $language->lang_echo('TYPE') ?></th>
				<th class='head1'><?php echo $language->lang_echo('STATUS') ?></th>
				<th class='head0'><?php echo $language->lang_echo('PRIORITY') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($this->get('projectTickets') as $ticket): ?>
				<tr>
					<td><?php echo $ticket['id'] ?></td>
					<td>
						<?php echo $this->displayLink('tickets.showTicket', $ticket['headline'], array('id'=>$ticket['id'])) ?>
					</td>
					<td class="center"><?php echo $ticket['type'] ?></td>
					<td class="center"><?php echo $ticket['status'] ?></td>
					<td class="center"><?php echo $ticket['priority'] ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>