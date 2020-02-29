<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );

$values = $this->get('values');
$helper = $this->get('helper');
?>


<div class="pageheader">
            <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php printf($language->lang_echo('EDIT_PROJECT'), $values['name']); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php $this->displayNotification() ?>

<form action="" method="post" class="stdform">
<div class="row-fluid">
	<div class="span6">
		
			
		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('PROJECT'); ?></h4>
		   <div class="widgetcontent">
		
		<p>
		<label for="name"><?php echo $language->lang_echo('NAME'); ?></label> 
		<span class='field'>
		<input type="text" name="name" id="name" class="input-large" value="<?php echo $values['name'] ?>" /><br />
		</span></p>
		
		<p>
		<label for="clientId"><?php echo $language->lang_echo('CLIENT'); ?></label> 
		<span class='field'>
		<select name="clientId" id="clientId">
		
			<?php foreach($this->get('clients') as $row){ ?>
			<option value="<?php echo $row['id']; ?>"
			<?php if($values['clientId'] == $row['id']){ ?> selected=selected
			<?php } ?>><?php echo $row['name']; ?></option>
			<?php } ?>
		
		</select> <br />
		</span></p>
		
		<!-- hour budget -->
		<p>
		<label for="hourBudget">Hour Budget</label> 
		<span class='field'>
			<input type="text" name="hourBudget" class="input-large" id="hourBudget" value="<?php echo $values['hourBudget'] ?>" /><br />
		</span></p>
		
		<label for="dollarBudget">Dollar Budget</label> 
		<span class='field'>
			<input type="text" name="dollarBudget" class="input-large" id="dollarBudget" value="<?php echo $values['dollarBudget'] ?>" /><br />
		</span></p>
		
		<p>
		<label for="projectState"><?php echo $language->lang_echo('PROJECTAPPROVAL'); ?></label>
		<span class='field'>
		<select name="projectState" id="projectState">
			<option value="0" <?php if($values['state'] == 0){ ?> selected=selected
			<?php } ?>><?php echo $language->lang_echo('OPEN'); ?></option>
		
			<option value="-1" <?php if($values['state'] == -1){ ?> selected=selected
			<?php } ?>><?php echo $language->lang_echo('CLOSED'); ?></option>
		
		</select> <br />
		</span></p>
			
			
			</div>
		</div>

</div>
<div class="span6">
			<div class="widget">
			   <h4 class="widgettitle"><?php echo $language->lang_echo('ASSIGN'); ?></h4>
			   <div class="widgetcontent">
			   	Choose the users that will have access to this project<br />
			   
				   	<div class="assign-container">
						<?php foreach($this->get('availableUsers') as $row){ ?>
							<?php if($_SESSION['userdata']['role'] == "user") { ?>
								
								<?php if(in_array($row['id'],explode(',',$values['editorId'])) || ($row['id'] == $_SESSION['userdata']["id"])){ ?>
									<p class="half">
										
										<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
											<?php if(in_array($row['id'],$values['assignedUsers'])): ?> checked="checked"<?php endif; ?>/>
										<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>		
									</p>
								<?php } ?>
							<?php }else{ ?>
								<p class="half">
									
									<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
										<?php if(in_array($row['id'],$values['assignedUsers'])): ?> checked="checked"<?php endif; ?>/>
									<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>		
								</p>
							<?php } ?>
							
						<?php } ?>
					</div>
				
				</div>
			</div>
			
	</div>

</div>
	

<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('PROJECT_DETAILS'); ?></h4>
   <div class="widgetcontent">


<p<span class='field'>
<textarea name="details" id="details" class="tinymce" rows="5" cols="50"><?php echo $values['details'] ?></textarea><br />
</span></p>

<p class='stdformbutton'>
	<input type="submit" name="save" id="save" class="button" value="<?php echo $language->lang_echo('SAVE'); ?>" class="button" />
</p>

	</div>
</div>

</form>


	</div>
</div>