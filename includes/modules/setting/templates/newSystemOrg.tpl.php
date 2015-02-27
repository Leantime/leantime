<?php $values = $this->get('values'); ?>


<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('CREATE_ORG'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
            	
<?php if($this->get('info') != ''){ ?>
<div class="fail"> 
	<span class="info"><?php echo $language->lang_echo($this->get('info')) ?></span> 
</div>
<?php } ?>

<form action="" method="post">
	
<p>
<label for="alias"><?php echo $language->lang_echo('ALIAS') ?></label> <input
	type="text" name="alias" id="alias" value="<?php echo $values['alias'] ?>"/><br />
</p><br />
<label for="name"><?php echo $language->lang_echo('NAME') ?></label> <input
	type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br />

<br />

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

<br /><br />
<input type="submit" name="save" id="save" value="<?php echo $language->lang_echo('SAVE') ?>" class="button" />

</form>

<div class="box-right">
<h3><?php echo $language->lang_echo('EDIT') ?></h3>
</div>

