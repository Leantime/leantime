
<?php
 defined( 'RESTRICTED' ) or die( 'Restricted access' );
 $values = $this->get('values');

?>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('PROJECT_DETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('NEW_PROJECT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification(); ?>


<form action="" method="post" class="stdform">
<div class="row-fluid">
	<div class="span6">


		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('PROJECT'); ?></h4>
		   <div class="widgetcontent">
		
			
			
				<p>
				<label for="name"><?php echo $lang['NAME']; ?></label> 
				<span class='field'><input type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br />
				</span></p>
		
				<p>
				<label for="clientId"><?php echo $lang['CLIENT']; ?></label> 
				<span class='field'>
					<select name="clientId" id="clientId">
				
				<?php foreach($this->get('clients') as $row){ ?>
			
				<option value="<?php echo $row['id']; ?>"
					<?php if($values['clientId'] == $row['id']){ ?> selected=selected
					<?php } ?>><?php echo $row['name']; ?></option>
				<?php } ?>
				<?php if(empty($row) === true) {?>
				<option value=""></option>
				<?php } ?>
					</select>
				</span> 
				</span></p>
				
				<p>
			    <label for="hourBudget">Hour Budget</label> 
			    <span class='field'>
			    <input type="text" name="hourBudget" id="hourBudget" value="0" /><br />
				</span></p>
				
				<p>
			    <label for="dollarBudget">Dollar Budget</label> 
			    <span class='field'>
			    <input type="text" name="dollarBudget" id="dollarBudget" value="0" /><br />
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

		<textarea name="details" id="details" class="tinymce" rows="5" cols="50"><?php echo $values['details'] ?></textarea><br />
		
				
		<p class='stdformbutton'>
			<input type="submit" name="save" id="save" value="<?php echo $lang['SUBMIT']; ?>" class="button" />
			<input type="reset" class="btn" value="<?php echo $language->lang_echo('RESET_BUTTON'); ?>" />
		</p>
		
	

	</div>
</div>

</form>
</div>
