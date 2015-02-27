<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$roles = $this->get('roles');
	$tabArray = $this->get('tabArray');
?>

<script type="text/javascript">

$(document).ready(function() 
    	{ 
      		
			
            
    	} 
	);   

</script>

<h1>Tabfreigabe für <?php echo $this->get('action'); ?></h1>
<br /><br />
<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $language->lang_echo($this->get('info')); ?></span> <?php } ?>

</div>

<form action="index.php?act=setting.editTabRights&amp;action=<?php echo base64_encode($this->get('action')); ?>&amp;rpc=true" method="post" class="nyroModal">


<fieldset style="min-height:200px;">


<table cellpadding="0" cellspacing="0" id="allTickets" class="allTickets" width="800px">
<thead>
<tr>
	<th align="left">Tab<br /><br /></th>
	<th align="left">Rollen<br /><br /></th>
</tr>
</thead>
<tbody>
<?php 		
	
	foreach($tabArray as $key => $value){
		
			echo'<tr class="jumpers '.$key.'">
					<td width="40%">'.$key.'</td>
					<td>';
				
				
				
				echo'<select name="'.$key.'-select[]" id="'.$key.'-select[]" size="10" multiple="multiple" style="width:300px;">';
				
				$oldSysOrg = '';
				$currentSysOrg = '';
				$i = 0;
				
				foreach($roles as $roleRow){
					
					$currentSysOrg = $roleRow['sysOrg'];
					
						//Show only Modules that are defined for that SysOrg

							if($oldSysOrg != $currentSysOrg){
								if($i > 0){
									echo'</optgroup>';
								}
								echo '<optgroup label="'. $roleRow['sysOrgName'].'">';
							}
							
							echo'<option value="'.$roleRow['roleName'].'"';
							
							if(in_array($roleRow['roleName'], $value))echo' selected="selected"';
							
							echo'>'.$roleRow['roleDescription'].'</option>';
						
						
						
						
						
					$i++;
					$oldSysOrg = $roleRow['sysOrg'];
					
					
				}
				echo'</select>';
			
			echo'</td>
				</tr>';
		
		
	
	}

?>

</tbody>


</table>

<input type="hidden" name="saveTabs" value=""/>

<input type="submit" name="saves" id="save" class="button" value="Speichern"/>

</fieldset>


</form>

