<?php 

$tickets = new tickets(); 
$helper = new helper();
$ticket = $this->get('ticket');
$statePlain = $this->get('statePlain');

?>

<!--<h3><?php echo $ticket['headline']; ?></h3><br />-->
<p><br />
	<?php if($ticket['dependingTicketId'] != 0): ?> 
		<?php echo $language->lang_echo('TICKET_DEPENDS_ON') ?>
		<?php $this->displayLink('tickets.showTicket', $ticket['dependingTicketId'], array('id' => $ticket['dependingTicketId'])) ?><br />
	<?php endif; ?>
</p>

<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered responsive">
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
		<col class="con0" />
	</colgroup>
	<thead>
		<tr>
			<th><?php echo $language->lang_echo('TICKET_ID'); ?></th>
			<th><?php echo $language->lang_echo('PROJECT'); ?></th>
			<th><?php echo $language->lang_echo('CLIENT'); ?></th>
			<th><?php echo $language->lang_echo('EDITOR'); ?></th>
			<th><?php echo $language->lang_echo('DATE_OF_TICKET'); ?></th>
			<th><?php echo $language->lang_echo('DATE_TO_FINISH'); ?></th>
			<th><?php echo $language->lang_echo('DATE_FROM').$lang['DATE_TO']; ?></th>
			<th><?php echo $language->lang_echo('PLAN_HOURS'); ?></th>
			<th><?php echo $language->lang_echo('TYPE'); ?></th>
			<th><?php echo $language->lang_echo('STATUS'); ?></th>
			<th><?php echo $language->lang_echo('PRIORITY'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>#<?php echo $ticket['id']; ?></td>
			<td><?php echo $ticket['projectName']; ?></td>
			<td><?php echo $ticket['clientName']; ?></td>
			<td><?php echo $ticket['editorFirstname']; ?> <?php echo $ticket['editorLastname']; ?></td>
			<td><?php echo $helper->timestamp2date($ticket['date'], 2); ?></td>
			<td><?php echo $helper->timestamp2date($ticket['dateToFinish'], 2); ?></td>
			<td><?php echo $helper->timestamp2date($ticket['editFrom'], 2); ?> -
			<?php echo $helper->timestamp2date($ticket['editTo'], 2); ?></td>
			<td><?php echo $ticket['planHours']; ?></td>
			<td><?php echo $ticket['type']; ?></td>
			<td>
				
				<div style="width:150px;" id="status-wrapper-<?php echo $ticket['id'] ?>" onclick="jQuery('#status-<?php echo $ticket['id'] ?>').toggle(); jQuery('#status-spinner-<?php echo $ticket['id'] ?>').toggle(); jQuery('#status-select-<?php echo $ticket['id'] ?>').toggle();">
					<span style="margin-left: 10px; width:100px; cursor:pointer;" id="status-<?php echo $ticket['id'] ?>" class="f-left <?php echo strtolower($tickets->getStatus($ticket['status']));?>">
						<?php echo $language->lang_echo($tickets->getStatusPlain($ticket['status'])); ?>
					</span>
					<span class='f-left statusButtons' id="status-spinner-<?php echo $ticket['id'] ?>">
						<div class="ui-spinner-button ui-spinner-up" ></div>
						<div class="ui-spinner-button ui-spinner-down"></div>
						<div class="clear">&nbsp;</div>
					</span>
				</div>
				
				<select style="display:none; width:150px;" onchange="changeStatus(<?php echo $ticket['id'] ?>)" id="status-select-<?php echo $ticket['id'] ?>" data-placeholder="<?php echo $language->lang_echo($tickets->getStatusPlain($ticket['status'])); ?>">
						<?php foreach($tickets->statePlain as $key => $row2) {?>
							<option value="<?php echo $key; ?>"
							<?php if($ticket['status'] == $key) {echo"selected='selected'";}?>
							><?php echo $language->lang_echo($tickets->getStatusPlain($key)); ?></option>
						<?php } ?>
				</select>
				
			
			</td>
			<td><?php echo $ticket['priority']; ?></td>
		</tr>
	</tbody>
</table>

<br />
			<?php echo $ticket['description']; ?><br />
<br />
