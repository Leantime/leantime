<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );

$values = $this->get('values');
$helper = $this->get('helper');
?>


<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('PROJECT_DETAILS'); ?></h5>
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
						<?php foreach($this->get('availableUsers') as $row): ?>
							<?php if($_SESSION['userdata']['role'] == "user") { ?>
								
								<?php if(in_array($row['id'],explode(',',$values['editorId'])) || ($row['id'] == $_SESSION['userdata']["id"])){ ?>
									<p class="half">
										
										<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
											<?php if(in_array($row['id'],$values['assignedUsers'])): ?> checked="checked"<?php endif; ?>/>
										<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>		
									</p>
								<? } ?>
							<?php }else{ ?>
								<p class="half">
									
									<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
										<?php if(in_array($row['id'],$values['assignedUsers'])): ?> checked="checked"<?php endif; ?>/>
									<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>		
								</p>
							<?php } ?>
						<?php endforeach; ?>
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
<!--
<div class="widget widget-half">
   <h4 class="widgettitle"><?php echo $language->lang_echo('ACCOUNTS'); ?></h4>
   <div class="widgetcontent">

<form action="" method="post" class="stdform">
<p>
<label for="accountName"><?php echo $language->lang_echo('LABEL'); ?></label> 
<span class='field'>
<input type="text" name="accountName" id="accountName" value="" /><br />
</span></p>

<p>
<label for="kind"><?php echo $language->lang_echo('ACCOUNT_KIND'); ?></label> 
<span class='field'>
<input type="text" name="kind" id="kind" value="" /><br />
</span></p>

<p>
<label for="username"><?php echo $language->lang_echo('USERNAME'); ?></label> 
<span class='field'>
<input type="text" name="username" id="username" value="" /><br />
</span></p>

<p>
<label for="password"><?php echo $language->lang_echo('PASSWORD'); ?></label> 
<span class='field'>
<input type="text" name="password" value="" /><br />
</span></p>

<p>
<label for="host"><?php echo $language->lang_echo('HOST'); ?></label> 
<span class='field'>
<input type="text" id="host" name="host" value="" /><br />
</span></p>

<p class='stdformbutton'>
	<input type="submit" name="accountSubmit" class="button" value="<?php echo $language->lang_echo('SUBMIT'); ?>" />
</p>

</form>
	</div>
</div>


<div class="widget widget-half">
   <h4 class="widgettitle"><?php echo $language->lang_echo('ATTACHMENTS'); ?></h4>
   <div class="widgetcontent">
   	
<form action="" method="post" enctype="multipart/form-data" class='stdform'>
	<div class="par">
	
	 <label><?php echo $language->lang_echo('UPLOAD_FILE') ?></label>
   	 
   	 <div class='fileupload fileupload-new' data-provides='fileupload'>
   	 	<input type="hidden" />
		<div class="input-append">
			<div class="uneditable-input span3">
				<i class="iconfa-file fileupload-exists"></i><span class="fileupload-preview"></span>
			</div>
			<span class="btn btn-file">
				<span class="fileupload-new">Select file</span>
				<span class='fileupload-exists'>Change</span>
				<input type='file' name='file' />
			</span>
				
			<a href='#' class='btn fileupload-exists' data-dismiss='fileupload'>Remove</a>
		</div>
  	</div>		
   </div>
		
	<p class='stdformbutton'>
		<input type="submit" name="upload" class="button" value="<?php echo $language->lang_echo('UPLOAD'); ?>" />
	</p>

	<p>
		
	</p>
	<hr /><br />

	<?php $row = '';
	foreach ($this->get('files') as $rowFiles) { ?> 
		<a href="downloads.php?id=<?php echo $rowFiles['id'] ?>&class=projects" target="_blank"><?php echo $rowFiles['realName']; ?></a><br />
		<?php printf("<span class=\"grey\">".$language->lang_echo('UPLOADED_BY_ON')."</span>", $rowFiles['firstname'], $rowFiles['lastname'], $helper->timestamp2date($rowFiles['date'], 2)); ?>
		<br /><br />
	<?php } ?> 
	<?php if(empty($rowFiles)){ ?> 
		<?php echo $language->lang_echo('ERROR_NO_FILES'); ?>
	<?php } ?>

</form>

	</div>
</div>
-->

	</div>
</div>