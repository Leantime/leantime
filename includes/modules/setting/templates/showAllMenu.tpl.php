<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$roles = $this->get('roles');
$helper = $this->get('helper');
$applications = $this->get('applications');
?>

<script type="text/javascript">
	$(document).ready(function() { 
		       
    });   
</script>

<script	src="includes/modules/general/templates/js/tableHandling.js" type="text/javascript"></script>

<div class="pageheader">
			
			<div class="actionButtons">            
            	<?php echo $this->displayLink('setting.addMenu', $language->lang_echo('ADD_MENU'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>
			</div>
			
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('ENTIRE_MENU'); ?></h1>
            </div>
			
			
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<form action="">


<table class="table-bordered table" "0" cellspacing="0" border="0" class="display" id="resultTable">
	<colgroup>
    	<col class="con0"/>
        <col class="con1" />
      	<col class="con0"/>
        <col class="con1" />
      	<col class="con0"/>
        <col class="con1" />
      	<col class="con0"/>
	</colgroup>
	<thead>
		<tr>
			<th class='head0'><?php echo $language->lang_echo('ID') ?></th>
			<th class='head1'><?php echo $language->lang_echo('NAME') ?></th>
			<th class='head0'><?php echo $language->lang_echo('MODULE') ?></th>
			<th class='head1'><?php echo $language->lang_echo('ACTION') ?></th>
			<th class='head0'><?php echo $language->lang_echo('ICON') ?></th>
			<th class='head1'><?php echo $language->lang_echo('EDIT') ?></th>
		</tr>
	</thead>
	<tbody>
	  <?php foreach($this->get('menu') as $row) { ?>
		<tr>
			<td class="center">
				<?php echo $this->displayLink('setting.editMenu',$row['id'],array('id' => $row['id'])) ?>
			</td>
			<td>
				<?php echo $this->displayLink('setting.editMenu',$row['name'],array('id' => $row['id'])); ?>
			</td>
			<td><?php echo $row['module'] ?></td>
			<td><?php echo $row['action'] ?></td>
			<td class="center"><span class="<?php echo $row['icon'] ?>"></span></td>
			<td class="center">
				<?php echo $this->displayLink(
									'setting.editMenu',
									'<span class="iconsweets-create"></span>',
									array('id' => $row['id'])) ?>
							
				<?php echo $this->displayLink(
									'setting.delMenu',
									'<span class="iconsweets-trashcan"></span>',
									array('id' => $row['id'])) ?>
			</td>
		</tr>
	  <?php } ?>
	</tbody>
</table>

</form>
