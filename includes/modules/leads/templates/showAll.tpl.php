

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        <h1><?php echo $language->lang_echo('LEADS'); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
	<div class="maincontentinner">
		
		<?php echo $this->displayLink('leads.addLead', $language->lang_echo('ADD_LEAD'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>
		
		<table class='table table-bordered' id='dyntable2'>
			<colgroup>
				<col class='con0' />
				<col class='con1' />
				<col class='con0' />
				<col class='con1' />
				<col class='con0' />
			</colgroup>
			<thead>
				<tr>
					<th class="head0 nosort"><input type="checkbox" class="checkall" /></th>
					<th class='head0'><?php echo $language->lang_echo('ID') ?></th>
					<th class='head1'><?php echo $language->lang_echo('NAME') ?></th>
					<th class='head0'><?php echo $language->lang_echo('STATUS') ?></th>
					<th class='head1'><?php echo $language->lang_echo('POTENTIAL_MONEY') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($this->get('leads') as $lead): ?>
				<tr>
					<td>
						<span class="center">
		                	<input type="checkbox" />
		           		</span>						
					</td>
					<td><?php echo $this->displayLink('leads.showLead',$lead['id'], array('id' => $lead['id'])) ?></td>
					<td><?php echo $this->displayLink('leads.showLead',$lead['name'], array('id' => $lead['id'])) ?></td>
					<td><?php echo $lead['status'] ?></td>
					<td>$<?php echo $lead['potentialMoney'] ?>.00</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	</div>
</div>