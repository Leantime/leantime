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
                <h1><?php echo $language->lang_echo('NEW_ROLE'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">



<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $language->lang_echo($this->get('info')) ?></span> <?php } ?>
</div>



<form action="" method="post" id="form" name="form">
<p>
<label for="roleName"><?php echo $language->lang_echo('ROLE_ALIAS') ?></label> <input
	type="text" name="roleName" id="roleName" value="<?php echo $values['roleName'] ?>"/><br />
</p><br />
<label for="roleDescription"><?php echo $language->lang_echo('DESCRIPTION') ?></label> <input
	type="text" name="roleDescription" id="roleDescription" value="<?php echo $values['roleDescription'] ?>" /><br />

<br />
<label for="sysOrg"><?php echo $language->lang_echo('SYSTEM_ORG') ?></label>
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

<label for="menu"><?php echo $language->lang_echo('STD_MENU') ?></label>
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
<a href="javascript:void(0)" onclick="checkElements(<?php echo count($this->get('menu')); ?>);"><?php $language->lang_echo('UNLOCK_ALL'); ?></a>


<br /><br />
<input type="submit" name="save" id="save"
	value="<?php echo $language->lang_echo('SAVE'); ?>" class="button" />

</form>

<div class="box-right">
	<h3><?php echo $language->lang_echo('EDIT'); ?></h3>
	<a href="index.php?act=setting.showAllRoles"><?php echo $language->lang_echo('BACK_TO_OVERVIEW'); ?></a>
</div>

			</div>
		</div>