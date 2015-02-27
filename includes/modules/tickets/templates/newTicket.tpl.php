<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$projects = $this->get('projects');
	$values = $this->get('values');
	$objTickets = $this->get('objTickets');
	$type = $this->get('type');
?>

<script type="text/javascript">
	$(function() {
		$("#dateToFinish, #editTo, #editFrom").datepicker({
			minDate: +1, 
			maxDate: '+1Y', 
			dateFormat: 'dd.mm.yy',
			dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
			dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
			monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
		});
		
	});
</script>

<style type='text/css'>
	.stdform label { width: 105px !important; }
		
</style>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('TICKETDETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('NEW_TICKET'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>

<form action="" method="post" enctype="multipart/form-data" class="stdform">

<div class="widget widget-half">
   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
   <div class="widgetcontent">

<label for="headline"><?php echo $language->lang_echo('HEADLINE') ?></label> <input
	type="text" name="headline" id="headline"
	value="<?php echo $values['headline']; ?>" /><br />

<label for='type'>Type</label>
			<select id='type' name='type'>
				<?php foreach($type as $types) {
					echo "<option value='".$types."' ";
						if($types === $values['type']) {
							echo "selected='selected'";
						}
					echo ">".$types."</option>";
				} ?>
			</select><br/>

<br/><label for="project"><?php echo $lang['PROJECT']; ?></label> 
<select id="project" name="project">
	<optgroup>
	<?php foreach($this->get('projects') as $row) {
		$currentClientName = $row['clientName'];

		if($currentClientName != $lastClientName){
			echo'</optgroup><optgroup label="'.$currentClientName.'">';
		}
		echo'<option value="'.$row['id'].'" ';
		if($values['projectId'] === $row['id'])echo'selected="selected"';
		echo'>'.$row['name'].'</option>';

		$lastClientName = $row['clientName'];

	}
	?>

	</optgroup>
	<?php if(empty($row) === true) {?>
	<option value=""></option>
	<?php } ?>
</select><br />

<label for="priority"><?php echo $lang['PRIORITY']; ?></label> <select
	id="prio" name="priority">

	<?php
	foreach($objTickets->priority as $key) {
		echo '<option value="'.$key.'"';
		if($key == $values['priority']) echo'selected="selected"';
		echo '>'.$key;
		if ($key == '1') {
			echo ' - lowest';
		} else if ($key == '6') {
			echo ' - highest';
		}
		echo '</option>';
	}
	?>

</select><br />

		
</div>
</div>

<div class="widget widget-half">
   <h4 class="widgettitle"><?php echo $language->lang_echo('TIMELINE'); ?></h4>
   <div class="widgetcontent">

<label for="dateToFinish"><?php echo $lang['DATE_TO_FINISH']; ?><small
	class="grey"><br />
(dd.mm.yyyy)</small></label> <input type="text" name="dateToFinish"
	id="dateToFinish" value="<?php echo $values['dateToFinish']; ?>" /><br />

	<?php if($this->get('role') !== 'client'){ ?> <br />
<label for="editFrom"><?php echo $lang['EDIT_FROM']; ?></label> <input
	type="text" name="editFrom" id="editFrom"
	value="<?php echo $values['editFrom']; ?>" /><br />
<label for="editTo"><?php echo $lang['EDIT_TO']; ?></label> <input
	type="text" name="editTo" id="editTo"
	value="<?php echo $values['editTo']; ?>" /><br />

		
	</div>
</div>

<div class="widget widget-half">
   <h4 class="widgettitle"><?php echo $language->lang_echo('ASSIGN'); ?></h4>
   <div class="widgetcontent">
   	
   	<div class='assign-container'>
	<?php foreach($this->get('availableUsers') as $row): ?>
		<p class='half'>
			<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>
			<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
				<?php if(in_array($row['id'],explode(',',$values['editorId']))): ?>checked="checked"<?php endif; ?>/>
		</p>
	<?php endforeach; ?>
	<br class='clear' />
	</div>

	
	</div>
</div>

	<?php } ?> 


<div class="widget widget-half">
   <h4 class="widgettitle"><?php echo $language->lang_echo('TECHNICAL_DETAILS'); ?></h4>
   <div class="widgetcontent">
   	
		
	<label for="os"><?php echo $lang['OPERATING_SYSTEM']; ?></label> 
	<select name="os" id="os">

	<?php foreach($objTickets->os as $row) {
		echo'<option value="'.$row.'" ';
		if($values['os'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	} ?>

	</select><br />

	<label for="browser"><?php echo $lang['BROWSER']; ?></label> 
	<select name="browser" id="browser">
	<?php foreach($objTickets->browser as $row) {
		echo'<option value="'.$row.'" ';
		if($values['browser'] == $row){
			echo'selected="selected"';
		}
		echo'> '.$lang[$row].'</option>';
	} ?>
	</select><br />

	<label for="resolution"><?php echo $lang['RESOLUTION']; ?></label> 
	<select name="resolution" id="resolution">

	<?php foreach($objTickets->res as $row) {
		echo'<option value="'.$row.'" ';
		if($values['resolution'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	} ?>

	</select><br />

	<label for="version"><?php echo $lang['VERSION']; ?></label> 
	<input type="text" name="version" id="version" value="<?php echo $values['version']; ?>" /><br />

	<label for="url"><?php echo $lang['URL']; ?></label> 
	<input type="text" name="url" id="url" value="<?php echo $values['url']; ?>" /><br />	
	

			

</div>
</div>

<div class="widget clear">
   <h4 class="widgettitle"><?php echo $language->lang_echo('DESCRIPTION'); ?></h4>
   <div class="widgetcontent">
   	

	<textarea placeholder="<?php echo $values['description'] ?>" id="elm1" name="description" rows="15" cols="80" style="width: 320px" class="tinymce"></textarea><br/>
	

	<input type="submit" name="save" id="save" value="<?php echo $lang['SAVE']; ?>" class="button" />
	<input type="reset" class="btn" value="<?php echo $language->lang_echo('RESET_BUTTON') ?>" />
	
	</div>
</div>
	
	</form>

	</div>
</div>

<!--<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('ATTACHMENTS'); ?></h4>
   <div class="widgetcontent">
	
	<form action="" method="post" enctype="multipart/form-data" class="stdform">
		
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
		
	</form>

	</div>
</div>-->
