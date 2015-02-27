<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	
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

</script>

<h1>Menü Vergabe</h1>

<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $language->lang_echo($this->get('info')); ?></span> <?php } ?>

</div>

<form action="index.php?act=setting.userMenu" method="post">
<fieldset class="left">
<legend>Filter</legend>
<table cellpadding="0" cellspacing="0">
	<tr>
	<td><label for="user">Benutzer</label></td>
	
	
	</tr>
	<tr>
	<td>
	<select name="user" style="width: 200px" id="user" onchange="submit();">
		<option value="">Benutzer</option>
		<?php foreach($this->get('users') as $res) { ?>
		<option value="<?php echo $res['id']; ?>" 
		<?php if($this->get('user') == $res['id'])echo'selected="selected"';?>>
		<?php echo $res['username']; ?> - 
		<?php echo $res['nachname']; ?>, 
		<?php echo $res['vorname']; ?></option>
		<?php } ?>
	</select>&nbsp;
	</td>
	
</tr>
</table>
</fieldset>

<div class="fieldsetleft" style="min-height:200px;">


<input type="submit" name="save" class="button" value="Änderung speichern" />


<table cellpadding="0" cellspacing="0" id="alleKostentraeger" class="allTickets">
<thead>
<tr>
	<th style="width:90px"><p>Zugewiesen<br />
	<a href=javascript:void(0);" onclick="check($('.checkbox'))">Select all</a></p>
	</th>
	<th>Menu</th>
</tr>
</thead>
<tbody>
<?php 
if($this->get('menu') != ""){
	
	$array = $this->get('menu');
	
	for($i=0; $i<count($array); $i++){
		
		
		
	
		echo'<tr>
		<td><input type="checkbox" name="menu[]" class="checkbox" value="'.$array[$i]["id"].'"
		';
		if($array[$i]['isRelated'] == '1') echo' checked="checked"';
		echo'/></td>
		<td>';
		
		
		if($array[$i]['parentName'] != ''){
			
			echo ''.$array[$i]['parentName'].' &rArr ';
			
		}
			
		echo''.$array[$i]['name'].'';
			
		echo'</td>
		</td>';
		
	
	}

}else{
	echo'<tr><td colspan="2">Keine Ergebnisse</td></tr>';
}
?>

</tbody>


</table>

</div>

<fieldset>
	<legend>Bearbeiten</legend>
<p>
<a href="javascript:window.print();">Übersicht drucken</a><br />	
<a href="index.php?act=setting.menuUser">Menüpunkt &rArr; Benutzer</a><br />
</p>
</fieldset>

</form>