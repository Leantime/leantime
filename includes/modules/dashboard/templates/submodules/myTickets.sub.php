
<style type='text/css'>
	.widgetbox .dataTables_filter { position: relative; top: -8px; right: 0; }
	.widgetbox #dyntable2_length { display: none; }
	.dataTables_filter {margin-top:0px}
	
</style>
<br />
<table id="dyntable2" class="table table-bordered">
	<colgroup>
        <col class="con1" />
      	<col class="con0"/>
	</colgroup>
	<thead>
		<tr>
			<th class='head1'><?php echo $language->lang_echo('TITLE') ?></th>
			<th class='head0'><?php echo $language->lang_echo('STATUS') ?></th>
		</tr>
	</thead>
	<tbody>
 	 <?php foreach($this->get('myTickets') as $ticket): ?>
		<tr>
			<td><?php echo $this->displayLink('tickets.showTicket', $ticket['headline'], array('id' => $ticket['id'])) ?></td>
			<td><?php echo $ticket['status'] ?></td>
		</tr>
	 <?php endforeach; ?>
	</tbody>
</table>