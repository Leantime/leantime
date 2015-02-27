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
	 document.form.elements['modules[]'].options[i].selected = selectedStatus;
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
                <h1><?php echo $language->lang_echo('SYSTEM_ORG_EDIT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $language->lang_echo($this->get('info')) ?></span> <?php } ?>

</div>

<form action="" method="post" id="form" name="form">
<p>
<label for="alias"><?php echo $language->lang_echo('ALIAS') ?></label> 
<input type="text" name="alias" id="alias" value="<?php echo $values['alias'] ?>"/><br />
</p><br />

<label for="name"><?php echo $language->lang_echo('NAME') ?></label> 
<input type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br /><br />

<label for="modules"><?php echo $language->lang_echo('MODULE') ?></label>
<select multiple="multiple" id="modules[]" name="modules[]" style="width:400px;" size="10">
	
	<?php foreach($this->get('modules') as $key => $row){?>
		<option value="<?php echo $key;?>"
			<?php if(in_array($key, $values['modules']) === true){ ?>
			 selected="selected" 
			<?php }?>
		>
			<?php echo $key; ?>
		</option>
		
		
	<?php } ?>
	

</select>
<a href="javascript:void(0)" onclick="checkElements(<?php echo count($this->get('modules'))?>);"><?php echo $language->lang_echo('UNLOCK_ALL') ?></a>

<br /><br />
<input type="submit" name="save" id="save"
	value="<?php echo $language->lang_echo('SAVE') ?>" class="button" />
</fieldset>

</form>

<div class="box-right">
<h3><?php echo $language->lang_echo('EDIT') ?></h3>
<a href="index.php?act=setting.delSystemOrg&amp;id=<?php echo $_GET['id']; ?>"><?php echo $language->lang_echo('CLEAR_ORG') ?></a>
</div>

