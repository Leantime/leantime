<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$roles = $this->get('roles');
$helper = $this->get('helper');
?>

<script type="text/javascript">
	$(document).ready(function() 
    	{ 
      

            
    	} 
	);   
</script>
<script	src="includes/modules/general/templates/js/tableHandling.js" type="text/javascript"></script>




<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('ALL_ROLES'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<div id="loader">&nbsp;</div>
<form action="">



<table class="table-bordered table" cellpadding="0" cellspacing="0" border="0"  class="display" id="resultTable">
	<colgroup>
      	  <col class="con0"/>
          <col class="con1" />
      	  <col class="con0"/>
          <col class="con1" />
    </colgroup>	
	<thead>
		<tr>
			<th>Id</th>
			<th><?php echo $language->lang_echo('ROLE_ALIAS') ?></th>
			<th><?php echo $language->lang_echo('DESCRIPTION') ?></th>
			<th><?php echo $language->lang_echo('SYSTEM_ORG') ?></th>
		</tr>
	</thead>

	<tbody>

	<?php foreach($this->get('allRoles') as $row) { ?>
		<tr>
			<td><a href="?act=setting.editRole&amp;id=<?php echo $row['id'] ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="?act=setting.editRole&amp;id=<?php echo $row['id'] ?>"><?php echo $row['roleName']; ?></a></td>
			<td><a href="?act=setting.editRole&amp;id=<?php echo $row['id'] ?>"><?php echo $row['roleDescription']; ?></a></td>
			<td><?php echo $row['sysOrgName']; ?></td>
			
		</tr>
		<?php } ?>

	</tbody>
</table>



<div class='box-right'>
	<h3><?php echo $language->lang_echo('EDIT') ?></h3> 
	<a href="index.php?act=setting.newRole"><?php echo $language->lang_echo('ADD_ROLE') ?></a>
</div>

</form>
	
				</div>
			</div>