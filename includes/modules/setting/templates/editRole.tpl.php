<?php

$values = $this->get('values');

?>

<script type="text/javascript">
var globalSelectedStatus = false;
//Elemente aus-/abw�hlen
function checkElements(numberOf) {
selectedStatus = (globalSelectedStatus) ? false : true;
globalSelectedStatus = (selectedStatus) ? true : false;

for (i = 0; i < numberOf; i ++) {
	 document.form.elements['menu[]'].options[i].selected = selectedStatus;
}

}

</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('EDIT_ROLE'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>
<form action='' method="POST">
<p>
<label for="roleName"><?php echo $language->lang_echo('ALIAS') ?></label> <input
	type="text" name="roleName" id="roleName" value="<?php echo $values['roleName'] ?>"/><br />
</p><br />
<label for="roleDescription"><?php echo $language->lang_echo('TITLE') ?></label> <input
	type="text" name="roleDescription" id="roleDescription" value="<?php echo $values['roleDescription'] ?>" /><br />

<br />
<label for="sysOrg"><?php echo $language->lang_echo('SYSTEM_ORGANIZATION') ?></label>
<select id="sysOrg" name="sysOrg" style="width:300px;">
<?php foreach($this->get('sysOrgs') as $row){?>
 <option value="<?php echo $row['id']; ?>"
 <?php if($values['sysOrg'] == $row['id']){ 
			echo ' selected="selected"';
			 } 
 
 ?> >
 	
	<?php echo $row['name']; ?> - <?php echo $row['alias']; ?> 
 
 </option>
<?php } ?>
</select>&nbsp;
<br />
<label for="template">Template</label>
<select id="template" name="template" style="width:300px;">
<?php foreach($this->get('templates') as $row){?>
 <option value="<?php echo $row; ?>"
 <?php if($values['template'] == $row){ 
			echo ' selected="selected"';
			 } 
 
 ?> >
 	
	<?php echo $row; ?>
 
 </option>
<?php } ?>
</select>&nbsp;

<br />
<label for="menu">Standardmenü</label>
<select id="menu[]" name="menu[]" multiple="multiple" size="10" style="width:300px;">
<?php foreach($this->get('menu') as $row){?>
 <option value="<?php echo $row['id']; ?>"
 <?php if(is_array($values['menu']) === true && in_array($row['id'], $values['menu']) === true){ 
			echo ' selected="selected"';
			 } 
 
 ?> >
 	<?php if($row['parentName'] != ''){?>
	<?php echo $row['parentName']; ?> &rarr;
	<?php } ?> 
	<?php echo $row['name']; ?> 
 
 </option>
<?php } ?>
</select>&nbsp;
<a href="javascript:void(0)" onclick="checkElements(<?php echo count($this->get('menu'))?>);"><?php echo $language->lang_echo('UNLOCK_ALL') ?></a>


<br /><br />
<input type="submit" name="save" id="save"
	value="<?php echo $language->lang_echo('SAVE') ?>" class="button" />
</fieldset>

</form>

<div class='box-right'>
	<legend><?php echo $language->lang_echo('EDIT') ?></legend>
	<?php echo $this->displayLink('setting.delRole',$language->lang_echo('DELETE_ROLE'),array('id' => (int)$_GET['id'])) ?>
</div>

</div>
</div>