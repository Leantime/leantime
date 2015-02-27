<?php

?>

<script	src="includes/modules/general/templates/js/tableHandling.js" type="text/javascript"></script>


	<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('ALL_CLIENTS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

			<form action="">
			
			
				<?php echo $this->displayLink('clients.newClient', $language->lang_echo('ADD_NEW_CLIENT'), NULL, array('class' => 'btn btn-primary btn-rounded')); ?>
			
			<table class='table table-bordered' cellspacing="0" border="0" class="display" id="dyntable2">
				<colgroup>
					
					<col class='con1' />
					<col class='con0' />
					<col class='con1' />
					<col class='con0' />
				</colgroup>
				<thead>
					<tr>
						
						<th class='head0'><?php echo $language->lang_echo('CLIENT_ID'); ?></th>
						<th class='head1'><?php echo $language->lang_echo('CLIENTNAME'); ?></th>
						<th class='head0'><?php echo $language->lang_echo('EMAIL'); ?></th>
						<th class='head1'><?php echo $language->lang_echo('NUMBER_PROJECTS'); ?></th>
					</tr>
				</thead>
				<tbody>
			
				<?php foreach($this->get('allClients') as $row) { ?>
					<tr>
						
						<td>
							<?php echo $this->displayLink('clients.showClient',$row['id'], array('id' => $row['id'])) ?>
						</td>
						<td>
							<?php echo $this->displayLink('clients.showClient',$row['name'], array('id' => $row['id'])) ?>
						</td>
						<td><a href="http://<?php echo $row['internet'] ?>" target="_blank"><?php echo $row['internet']; ?></a></td>
						<td><?php echo $row['numberOfProjects']; ?></td>
					</tr>
					<?php } ?>
			
				</tbody>
			</table>
				
			</form>
	
		</div>
	</div>