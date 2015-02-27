<?php

$values = $this->get('values');

?>

<div class="pageheader">
           
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('MENU'); ?></h5>
                <h1><?php echo $language->lang_echo('ADD_MENU') ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
			
			<?php echo $this->displayNotification() ?>
			
			<div class="widget">
			   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
			   <div class="widgetcontent">
				
				

				<form action="" method="post" class="stdform">
					
				<p>
				<label for="name"><?php echo $language->lang_echo('NAME') ?></label> 
				<input type="text" name="name" id="name" value="<?php echo $values['name'] ?>"/><br /><br />

				<label for="">
					<a href='/includes/templates/zypro/buttons.html#iconsweets' target='_blank'>
						<?php echo $language->lang_echo('ICONS') ?>
					</a>
				</label>
				<input type='text' name='icon' value='<?php echo $values['icon'] ?>' /><br />
				
				<?php $moduleLinks = $this->get('moduleLinks'); ?>
				<label><?php echo $language->lang_echo('MODULE') ?></label>
				<select style="width:300px;" name="module" id="moduleLink" onchange="$('#link').val($('#moduleLink option:selected').val());">
					<?php foreach($moduleLinks as $key => $row): ?>
						<optgroup label="<?php echo $key ?>">
							<?php foreach($row as $row2): ?>
								<option value="<?php echo $row2 ?>"><?php echo $row2 ?></option>';			
							<?php endforeach; ?>
						</optgroup>	
					<?php endforeach; ?>
				</select><br/>
				
				<label for="parent"><?php echo $language->lang_echo('PARENT_ELEMENT') ?></label>
				<select id="parent" name="parent">
					<option value="0"><?php echo $language->lang_echo('NO_PARENT') ?></option>
					<?php foreach ($this->get('wholeMenu') as $row): ?>
						<option value="<?php echo $row['id'] ?>"
						<?php if ($values['parent'] == $row['id']) 
							echo ' selected="selected" '; ?>
						>
						<?php echo $row['name']; ?> 
						</option>
					<?php endforeach; ?>
				</select><br /><br />
				
				<p class='stdformbutton'>
					<input type="submit" name="save" id="save" value="<?php echo $language->lang_echo('SAVE') ?>" class="button" />
				</p>
				
				</form>

				</div>
			  </div>
			</div>
		</div>
