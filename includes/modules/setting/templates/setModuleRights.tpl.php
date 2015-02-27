<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$roles = $this->get('roles');
	$rightsArray = $this->get('rightsArray');
	$array = $this->get('modules');
	$thisObject = $this->get("this");
?>

<script type="text/javascript">


var checkflag = "false";

function check(field) {
	if (checkflag == "false") {
	  for (i = 0; i < field.length; i++) {
	  field[i].checked = true;}
	  checkflag = "true";
	  return " keine "; }
	else {
	  for (i = 0; i < field.length; i++) {
	  field[i].checked = false; }
	  checkflag = "false";
	  return " alle "; }
}

function allPublic() {

	var bestaetigung = window.confirm('Sollen wirklich alle Module freigeschaltet werden?');
	//Nach Betätigung des Buttons
	if(bestaetigung) {
	  //OK wurde gedrückt
	 
	  	$('select.visible option').attr('selected', 'selected');
	 
	  
	}
	else {
	  //Abbrechen wurde gedrückt
	}
	
}

function allClosed() {

	var bestaetigung = window.confirm('Sollen wirklich alle Module gesperrt werden?');
	//Nach Betätigung des Buttons
	if(bestaetigung) {
	  //OK wurde gedrückt
	 
	 	 $('select.visible option:selected').removeAttr('selected');
	  
	}
	else {
	  //Abbrechen wurde gedrückt
	}
	
}

function removeModules(modules){
	$('#loading').fadeIn();
	$('#actions, #allTickets').fadeOut();
	
	
	$('.jumpers').hide();
	$('select').attr('class', 'invisible');
	
	//Entferne aus Sprungmarken
	var moduleArr = modules.split(',');

	for(i=0; i < moduleArr.length; i++){

		if(moduleArr[i] != ''){
			$('.'+moduleArr[i]+'').show();
			$('.'+moduleArr[i]+' select').attr('class', 'visible');
		}
		
	}
	
	$('#actions, #allTickets').fadeIn();
	$('#loading').fadeOut();
	
}

$(document).ready(function() 
    	{ 
      
			
            $('.lightBox').nyroModal({});
    	} 
	);   

</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('JUMP_TO'); ?></h5>
                <h1><?php echo $language->lang_echo('SHARING_MODULES'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $language->lang_echo($this->get('info')); ?></span> <?php } ?>

</div>

<form action="" method="post">

<!--label for="sysOrgs">Systemorganisation</label>
<select id="sysOrgs" name="sysOrgs" onchange="removeModules($('#sysOrgs option:selected').val());">

	<?php 
		foreach($this->get('sysOrgs') as $row){
			echo'<option value="'.$row['modules'].'">'.$row['name'].'</option>';
		}
	?>
</select
<br /><br /-->
<label><?php echo $language->lang_echo('MODULE') ?></label>
<div id="actions">
	<select id="module" onchange="$(window).scrollTop($('#'+ $('#module option:selected').val() +'').position().top)
">
		
		<?php 
		
		foreach($array as $key => $value){
			echo'<option value="'.$key.'" class="jumpers '.$key.'">'.$key.'  </option>';
		}
		
		?>
			
	</select>
</div>

<?php 
		
		/*foreach($array as $key => $value){
			echo'<a href="#'.$key.'" class="jumpers '.$key.'">'.$key.'  | </a>';
		}*/
		
		?>


<input type="submit" name="save" class="button" value="<?php echo $language->lang_echo('SAVE_CHANGES') ?>" />

<div class="right">
	<?php echo $language->lang_echo('QUICK_SELECTION') ?>: <a href="javascript:void(0)" onclick="allPublic();"><?php echo $language->lang_echo('RELEASE_ALL') ?></a> |
	<a href="javascript:void(0)" onclick="allClosed();"><?php echo $language->lang_echo('COLLAPSE_BLOCK') ?></a> |
	<a href="index.php?act=setting.setModuleRights"><?php echo $language->lang_echo('RESET') ?></a>
	
</div>

<div id="loading" style="text-align:center; display:none; background:#ddd; border:1px solid #ccc; padding:10px;">
<p>...Lade...</p>
</div>

<table class="table table-bordered" "0" cellspacing="0" id="allTickets" class="allTickets">
<colgroup>
      	  <col class="con0"/>
          <col class="con1" />
</colgroup>
<thead>
<tr>
	<th><?php echo $language->lang_echo('MODULE') ?></th>
	<th><?php echo $language->lang_echo('ROLES') ?></th>
</tr>
</thead>
<tbody>
<?php 
if($this->get('modules') != ""){
	
	
		
	
	foreach($array as $key => $value){

		echo'<tr class="jumpers '.$key.'">
		
		<td style="background:#ccc; font-weight:bold;" id="'.$key.'">';
		
		echo'<a href="#top">'.$key.' (&uarr; nach oben )</a>';
			
		echo'</td>
		<td style="background:#ccc; font-weight:bold;">
		</td>
		</tr>';
		
		
		foreach($value as $row) {
			echo'<tr class="jumpers '.$key.'">';
			
			if($thisObject->hasTabRights($key.'/'.$row) === true){
					echo'<td><a href="index.php?act=setting.editTabRights&amp;action='.base64_encode($key.'/'.$row).'&amp;rpc=true" class="lightBox">'.$row.'</a></td>';
			}else{
					echo'<td>'.$row.'</td>';
			}
					
			echo'		<td>';
				
				$moduleName = str_replace(".", "-", $row);
				
				echo'<select name="'.$key.'-'.$moduleName.'-select[]" id="'.$key.'-'.$moduleName.'-select[]" size="10" multiple="multiple" style="width:300px;">';
				
				$oldSysOrg = '';
				$currentSysOrg = '';
				$i = 0;
				foreach($roles as $roleRow){
					
					$currentSysOrg = $roleRow['sysOrg'];
					
						//Show only Modules that are defined for that SysOrg
						
						if(in_array($key, explode(',', $roleRow['modules'] ))=== true){

							if($oldSysOrg != $currentSysOrg){
								if($i > 0){
									echo'</optgroup>';
								}
								echo '<optgroup label="'. $roleRow['sysOrgName'].'">';
							}
							
							echo'<option value="'.$roleRow['id'].'"';
							
							if 	( 
								 	isset($rightsArray[$key.'/'.$row]) === true 
								 	&& in_array($roleRow['id'], $rightsArray[$key.'/'.$row])
								) echo' selected="selected"';
							
							echo'>'.$roleRow['roleDescription'].'</option>';
						}
						
						
						
						
					$i++;
					$oldSysOrg = $roleRow['sysOrg'];
					
					
				}
				echo'</select>';
			
			echo'</td>
				</tr>';
		}
		
	
	}

}else{
	echo'<tr><td colspan="2">Keine Ergebnisse</td></tr>';
}
?>

</tbody>


</table>

</div>

<div class="box-right">
	<legend><?php echo $language->lang_echo('EDIT') ?></legend>
	<p>
		<a href="javascript:window.print();"><?php echo $language->lang_echo('PRINT') ?></a><br />	
	</p>
</div>

</form>

				</div>
			</div>
