<?php 
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$roles = $this->get('roles');
?>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW') ?></h5>
                <h1><h1><?php echo $language->lang_echo('ALL_USER'); ?></h1></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>
				
				<?php echo $this->displayLink('users.newUser', $language->lang_echo('ADD_USER'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>
				
				<table cellpadding="0" cellspacing="0" border="0" class='table table-bordered' id='dyntable2'>
					<colgroup>
				   		<col class="con0"/>
				        <col class="con1" />
				   		<col class="con0"/>
				    </colgroup>	
					<thead>
						<tr>
							<th class='head0'><?php echo $language->lang_echo('ID'); ?></th>
							<th class='head1'><?php echo $language->lang_echo('NAME'); ?></th>
							<th class='head0'><?php echo $language->lang_echo('ROLE'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($this->get('allUsers') as $row): ?>
							<tr>
								<td><?php echo $this->displayLink('users.editUser', $row['id'], array('id' => $row['id'])) ?></td>
								<td><?php echo $this->displayLink('users.editUser', $row['firstname'].' '.$row['lastname'], array('id' => $row['id'])) ?></td>
								<td><?php echo $row['roleName']; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

