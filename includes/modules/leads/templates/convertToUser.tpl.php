<?php $values = $this->get('values') ?>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('LEAD_DETAILS'); ?></h5>
        <h1><?php echo $language->lang_echo('CONVERT_TO_USER'); ?></h1>
    </div>
</div><!--pageheader-->      
<div class="maincontent">
	<div class="maincontentinner">
	            	
	    <?php echo $this->displayNotification() ?>

		<form action='' method='POST' class="stdform" enctype="multipart/form-data">
					
		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
		   <div class="widgetcontent">		
		   	
				<label for="firstname"><?php echo $language->lang_echo('FIRSTNAME'); ?></label> 
				<input type="text" name="firstname" id="firstname" value="<?php echo $values['firstname'] ?>" /><br />
				
				<label for="lastname"><?php echo $language->lang_echo('LASTNAME'); ?></label> 
				<input type="text" name="lastname" id="lastname" value="<?php echo $values['lastname'] ?>" /><br />
				
				<label for="user"><?php echo $language->lang_echo('EMAIL'); ?></label> 
				<input type="text" name="user" id="user" value="<?php echo $values['user'] ?>" /><br />
				
				<label for="password"><?php echo $language->lang_echo('PASSWORD'); ?></label> 
				<input type="text" name="password" id="password" value="<?php echo $values['password'] ?>" /><br />
				
				<label for="phone"><?php echo $language->lang_echo('PHONE'); ?></label> 
				<input type="text" name="phone" id="phone" value="<?php echo $values['phone'] ?>" /><br />
				
				<label for="role"><?php echo $language->lang_echo('ROLE'); ?></label> 
				<select name="role" id="role">
					<?php foreach($this->get('roles') as $role){ ?>				
						<option value="<?php echo $role['id']; ?>" title="<?php echo $role['roleDescription']; ?>"
							<?php if($role['id'] == $values['role']): ?> selected='selected' <?php endif; ?>	
						>
							<?php echo $role['roleDescription']; ?>			
						</option>
					<?php } ?>
				</select> <br />
				
				<label for="client"><?php echo $language->lang_echo('CLIENT') ?></label>
				<select name='clientId' id="client">
					<?php foreach($this->get('clients') as $client): ?>
						<option value="<?php echo $client['id'] ?>"
							<?php if ($client['id'] == $values['clientId']): ?> selected='selected' <?php endif; ?>	
						>
							<?php echo $client['name'] ?>
						</option>
					<?php endforeach; ?>
				</select><br/>
		   	
		   		<p class="stdformbutton">
		   			<input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='save' />
		   		</p>
		   	
		   </div>
		</div>

	</div>
</div>