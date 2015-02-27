<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$project = $this->get('project');
?>

<script	src="includes/modules/general/templates/js/tableHandling.js" type="text/javascript"></script>

		<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('ALL_PROJECTS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


		<?php echo $this->displayLink('projects.newProject',$language->lang_echo('NEW_PROJECT'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>
			
		<table class='table table-bordered' cellpadding="0" cellspacing="0" border="0" class="table table-bordered" id="dyntable2">
			<colgroup>
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		      	<col class="con1" />
		    </colgroup>
			<thead>
				<tr>
					<th class="head0 nosort"><input type="checkbox" class="checkall" /></th>
					<th class="head0"><?php echo $language->lang_echo('NAME'); ?></th>
					<th class="head1"><?php echo $language->lang_echo('CLIENT'); ?></th>
					<th class="head0"><?php echo $language->lang_echo('NUMBER_OF_TICKETS'); ?></th>
					<th class="head1"><?php echo $language->lang_echo('BUDGET_HOURS') ?></th>
					<th class="head1"><?php echo $language->lang_echo('BUDGET_DOLLARS') ?></th>
				</tr>
			</thead>
			<tbody>
		
			 <?php foreach($this->get('allProjects') as $row): ?>
				<tr class='gradeA'>
					<td class="aligncenter">
						<span class="center">
		                	<input type="checkbox" />
		           		</span>
		           	</td>
					<td>
						<?php echo $this->displayLink('projects.showProject',$row['name'], array('id' => $row['id'])) ?>
					<td>
						<?php echo $this->displayLink('clients.showClient',$row['clientName'], array('id' => $row['clientId']), NULL, true) ?>
					</td>
					<td class="center"><?php echo $row['numberOfTickets']; ?></td>
					<td class="center"><?php echo $row['hourBudget']; ?></td>
					<td class="center"><?php echo $row['dollarBudget']; ?></td>
				</tr>
			 <?php endforeach; ?>
		
			</tbody>
		</table>

			</div>
		</div>