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

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('FILTER'); ?></h5>
                <h1><?php echo $language->lang_echo('USER_MENU'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $language->lang_echo($this->get('info')); ?></span> <?php } ?>

</div>

<form action="index.php?act=setting.menuUser" method="post">
<table cellpadding="0" cellspacing="0">
	<tr>
	<td><label for="menu"><?php echo $language->lang_echo('MENU_ITEM') ?></label></td>
	
	
	</tr>
	<tr>
	<td>
	<select name="menu" style="width: 200px" id="user" onchange="submit();">
		<option value="">Menüpunkt</option>
		<?php foreach($this->get('menues') as $res) { ?>
		<option value="<?php echo $res['id']; ?>" <?php if($this->get('menu') == $res['id'])echo'selected="selected"';?>><?php if($res['parentName'] != '') echo $res['parentName'].' &rArr; '; ?><?php echo $res['name']; ?>  </option>
		<?php } ?>
	</select>&nbsp;
	</td>
	
</tr>
</table>

<div class="fieldsetleft" style="min-height:200px;">


<input type="submit" name="save" class="button" value="<?php echo $language->lang_echo('SAVE_CHANGES') ?>" />


<table cellpadding="0" cellspacing="0" id="alleKostentraeger" class="allTickets">
<thead>
<tr>
	<th style="width:90px"><p><?php echo $language->lang_echo('ASSIGNED') ?><br />
	<a href=javascript:void(0);" onclick="check($('.checkbox'))"><?php echo $language->lang_echo('SELECT_ALL') ?></a></p>
	</th>
	<th><?php echo $language->lang_echo('USER') ?></th>
</tr>
</thead>
<tbody>
<?php 
if($this->get('users') != ""){
	
	$array = $this->get('users');
	
	for($i=0; $i<count($array); $i++){
		
		
		
	
		echo'<tr>
		<td><input type="checkbox" name="user[]" class="checkbox" value="'.$array[$i]["id"].'"
		';
		if($array[$i]['isRelated'] == '1') echo' checked="checked"';
		echo'/></td>
		<td>';
		
		
		echo ''.$array[$i]['username'].' - ';
		
		echo ''.$array[$i]['nachname'].' ';
			
		
			
		echo''.$array[$i]['vorname'].'';
			
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
<div class='box-right'>
	<h3><?php echo $language->lang_echo('EDIT') ?></h3>
	<p>
		<a href="javascript:window.print();"><?php echo $language->lang_echo('PRINT') ?></a><br />	
		<a href="index.php?act=setting.userMenu"><?php echo $language->lang_echo('USER') ?> &rArr; <?php echo $language->lang_echo('MENU') ?></a><br />
	</p>
</div>


</form>