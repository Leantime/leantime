<?php $values = $this->get('values'); ?>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('MENU_DETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('EDIT_MENU'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>

<form action="" method="post" class='stdform'>
<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
   <div class="widgetcontent">
   
				
							
<p>
<label for="name"><?php echo $language->lang_echo('NAME') ?></label> 
<input type="text" name="name" id="name" value="<?php echo $values['name'] ?>"/><br /><br />

<?php $moduleLinks = $this->get('moduleLinks'); ?>
<label><?php echo $language->lang_echo('MODULE') ?></label>
<select style="width:300px;" id="moduleLink" name='module' onchange="$('#link').val($('#moduleLink option:selected').val());">
	
	<?php foreach($moduleLinks as $key => $row): ?>
		<optgroup label="<?php echo $key ?>">
					
			<?php foreach ($row as $row2): ?>
				<option value="<?php echo $row2 ?>" <?php if (strpos($row2, $values['module'].'.'.$values['action']) !== false): ?> selected='selected' <?php endif; ?>>
					<?php echo $row2 ?>
				</option>
			<?php endforeach; ?>
		</optgroup>
	<?php endforeach; ?>
</select><br/>

<label for="">
	<a href='/includes/templates/zypro/buttons.html#iconsweets' target='_blank'>
		<?php echo $language->lang_echo('ICONS') ?>
	</a>
</label>
<input type='text' name='icon' value='<?php echo $values['icon'] ?>' /><br />

<label for="parent"><?php echo $language->lang_echo('PARENT') ?></label>
<select id="parent" name="parent">
	<option value=""><?php echo $language->lang_echo('NO_PARENT') ?></option></option>
	<?php foreach($this->get('wholeMenu') as $row): 
		echo'<option value="'.$row['id'].'"';
			if($values['parent'] == $row['id']) echo' selected="selected"';
		echo'>';?>
			<?php echo $row['name']; ?> 
		</option>
	<?php endforeach; ?>
</select><br /><br /><br />

<p class='stdformbutton'>
	<input type="submit" name="save" id="save" value="<?php echo $language->lang_echo('SAVE') ?>" class="button" />
	<?php echo $this->displayLink(
								'setting.delMenu', 
								$language->lang_echo('DELETE'), 
								array('id' => (int)$_GET['id']), 
								array('class' => 'btn btn btn-danger btn-rounded')
						) ?>
</p>

</div>
</div>
</form>

