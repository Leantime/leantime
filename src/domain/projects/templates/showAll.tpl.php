<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$project = $this->get('project');
?>

<script type="text/javascript">
    jQuery(document).ready(function() {

            <?php if(isset($_SESSION['userdata']['settings']["modals"]["showProjects"]) === false || $_SESSION['userdata']['settings']["modals"]["showProjects"] == 0){     ?>
            leantime.helperController.showHelperModal("showProjects");
            <?php
                //Only show once per session
                $_SESSION['userdata']['settings']["modals"]["showProjects"] = 1;
            } ?>

        }
    );

</script>

		<div class="pageheader">
                        
            <div class="pageicon"><span class="fa fa-suitcase"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1>All Projects</h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


		<?php echo $this->displayLink('projects.newProject',"<i class='iconfa-plus'></i> ".$language->lang_echo('NEW_PROJECT'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>

        <h4 class="widgettitle">Project List</h4>
		<table class='table table-bordered' cellpadding="0" cellspacing="0" border="0" class="table table-bordered" id="dyntable2">
			<colgroup>
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		    </colgroup>
			<thead>
				<tr>
					<th class="head0"><?php echo $language->lang_echo('NAME'); ?></th>
					<th class="head1"><?php echo $language->lang_echo('CLIENT'); ?></th>
                    <th class="head1">Status</th>
					<th class="head0"><?php echo $language->lang_echo('NUMBER_OF_TICKETS'); ?></th>
					<th class="head1"><?php echo $language->lang_echo('BUDGET_HOURS') ?></th>
					<th class="head0"><?php echo $language->lang_echo('BUDGET_DOLLARS') ?></th>
				</tr>
			</thead>
			<tbody>
		
			 <?php foreach($this->get('allProjects') as $row): ?>
				<tr class='gradeA'>
					
					<td>
						<?php echo $this->displayLink('projects.showProject',$row['name'], array('id' => $row['id'])) ?>
					<td>
						<?php echo $this->displayLink('clients.showClient',$row['clientName'], array('id' => $row['clientId']), NULL, true) ?>
					</td>
                    <td class="center"><?php if($row['state'] == -1) echo "Closed"; else { echo "Active"; } ?></td>
					<td class="center"><?php echo $row['numberOfTickets']; ?></td>
					<td class="center"><?php echo $row['hourBudget']; ?></td>
					<td class="center"><?php echo $row['dollarBudget']; ?></td>
				</tr>
			 <?php endforeach; ?>
		
			</tbody>
		</table>

			</div>
		</div>