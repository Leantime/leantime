<?php
	$status = $this->get('status');
	$values = $this->get('values');
	$projects = $this->get('relations');
?>
<script type="text/javascript">
	
	jQuery(document).ready(function(){
		// Dual Box Select
		var db = jQuery('#dualselect').find('.ds_arrow button');	//get arrows of dual select
		var sel1 = jQuery('#dualselect select#selectOrigin');		//get first select element
		var sel2 = jQuery('#dualselect select#selectDest');			//get second select element
		var projects = jQuery('#projects');		
		//sel2.empty(); //empty it first from dom.
		
		db.click(function(){
			
			var t = (jQuery(this).hasClass('ds_prev'))? 0 : 1;	// 0 if arrow prev otherwise arrow next
			
			if(t) {
				
				sel1.find('option').each(function(){
				
					if(jQuery(this).is(':selected')) {
						
						jQuery(this).attr('selected',false);
						
						
						
						sel2.append(jQuery(this).clone());
						
						jQuery('#projects').append(jQuery(this));
						
						jQuery('#projects option').attr("selected", "selected");
					
					}
				
				});	
				
				
			} else {
				sel2.find('option').each(function(){
					
					if(jQuery(this).is(':selected')) {
						
						jQuery(this).attr('selected',false);
						index = jQuery(this).index();
						alert(index)
						sel1.append(jQuery(this));
						
						jQuery('#projects option:eq('+index+')').remove();
						
						jQuery('#projects option').attr("selected", "selected");
					
					}
				});		
			}
			
			
			return false;
		});	
		
		
	});
	
</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('USER_DETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('EDIT_USER'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<div class="row-fluid">
	<span class="span12"><?php echo $this->displayNotification() ?></span>
</div>

<div class="row-fluid">
	<form action="" method="post" class="stdform">
	<span class="span6">
		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
		   <div class="widgetcontent">

		<label for="firstname"><?php echo $language->lang_echo('FIRSTNAME'); ?></label> <input
			type="text" name="firstname" id="firstname"
			value="<?php echo $values['firstname'] ?>" /><br />
		
		<label for="lastname"><?php echo $language->lang_echo('LASTNAME'); ?></label> <input
			type="text" name="lastname" id="lastname"
			value="<?php echo $values['lastname'] ?>" /><br />
		
		<label for="user"><?php echo $language->lang_echo('EMAIL'); ?></label> <input
			type="text" name="user" id="user" value="<?php echo $values['user'] ?>" /><br />
		
		<label for="phone"><?php echo $language->lang_echo('PHONE'); ?></label> <input
			type="text" name="phone" id="phone"
			value="<?php echo $values['phone'] ?>" /><br />
		
		<label for="status">Status</label> <select name='status' id='status'>
			<?php foreach($status as $key => $value) { ?>
				<option value='<?php echo $key ?>' 
					<?php if($key == $values['status']) { ?> selected='selected' <?php } ?>>
					<?php echo $key ?>
				</option>
			<?php } ?>
		</select><br />
		
		<label for="role"><?php echo $language->lang_echo('ROLE'); ?></label> <select
			name="role" id="role">
			<?php foreach($this->get('roles') as $role){ ?>
				<option value="<?php  echo $role['id']; ?>"
					<?php if($role['id'] == $values['role']){ ?> selected="selected" <?php } ?>>
					<?php echo $role['roleDescription']; ?>
				</option>
			<?php } ?>
		</select> <br />
		
		<label for="client"><?php echo $language->lang_echo('CLIENT') ?></label>
		<select name='client' id="client">
			<option value="0" selected="selected"><?php echo $language->lang_echo('NO_CLIENTS') ?></option>
			<?php foreach($this->get('clients') as $client): ?>
				<option value="<?php echo $client['id'] ?>" <?php if ($client['id'] == $values['clientId']): ?>selected="selected"<?php endif; ?>>
					<?php echo $client['name'] ?>
				</option>
			<?php endforeach; ?>
		</select><br/>
		
		
			
			<label><?php echo $language->lang_echo('HOURS') ?></label>
			<input type='text' name='hours' value='<?php echo $values['hours'] ?>' /><br />
			
			<label><?php echo $language->lang_echo('WAGE') ?></label>
									
			<div class="input-prepend input-append">
		   	<span class="add-on">$</span>
		    <input type='text' name='wage' value='<?php echo $values['wage'] ?>' />
		    <span class="add-on">.00</span>
		    </div>
			

		
		<div class="stdformbutton">
		<input type="submit" name="save" id="save"
			value="<?php echo $language->lang_echo('SAVE'); ?>" class="button" />
		</div>



			</div>
		</div>
		
	</span>
	
	
	<span class="span6">
	<div class="widget ">
   <h4 class="widgettitle"><?php echo $language->lang_echo('RELATED_PROJECTS'); ?></h4>
   <div class="widgetcontent">
		
		 <span id="dualselect" class="dualselect" style="margin-left:20px;">
		 						
                            	<select class="uniformselect" name="select3" multiple="multiple" size="10" id="selectOrigin">

										<?php foreach($this->get('allProjects') as $row){ ?>
											<?php if(is_array($projects) === true && in_array($row['id'], $projects) === false){ ?>
												<option value="<?php echo $row['id'] ?>"><?php echo $row['clientName']; ?> / <?php echo $row['name']; ?></option>
											<?php } ?>
										<?php } ?>

                                </select>
                                
                                <span class="ds_arrow">
                                    <button class="btn ds_prev"><i class="iconfa-chevron-left"></i></button><br />
                                    <button class="btn ds_next"><i class="iconfa-chevron-right"></i></button>
                                </span>
                               
                                <select name="select4" multiple="multiple" size="10" id="selectDest">
                                    
                                  		
										<?php foreach($this->get('allProjects') as $row){ ?>
											<?php if(is_array($projects) === true && in_array($row['id'], $projects) === true){ ?>
												<option value="<?php echo $row['id'] ?>"><?php echo $row['clientName']; ?> / <?php echo $row['name']; ?></option>
											
											<?php } ?>
												
									<?php } ?>
                                </select>
                            </span>
                            
                            <select name="projects[]" multiple="multiple" size="10" id="projects" style="display:none;">
                                   
                                  		
										<?php foreach($this->get('allProjects') as $row){ ?>
									 		<?php if(is_array($projects) === true && in_array($row['id'], $projects) === true){ ?>
												<option value="<?php echo $row['id'] ?>" selected="selected"><?php echo $row['clientName']; ?> / <?php echo $row['name']; ?></option>											
											<?php } ?>
		
									<?php } ?>
                                </select>
                            
                            
		
		
	
		
		
		
	
</div>
</div>

		
		
		
		
	</span>
	</form>
	
	
	
</div>




		
		
		
		
		
		
		
			</div>
		</div>
