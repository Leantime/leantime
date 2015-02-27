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
<!-- TinyMCE -->
<script
	type="text/javascript"
	src="includes/libs/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
		editor_deselector : "mceNoEditor",
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|styleselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,anchor,image,cleanup",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid",
		theme_advanced_buttons4 : "sub,sup,cite,|,abbr,acronym,del,ins,|,nonbreaking,restoredraft,|,charmap,emotions,iespell,media",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
</script>
<!-- /TinyMCE -->

<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$projects = $this->get('projects');
$values = $this->get('values');
$objTickets = $this->get('objTickets');
?>

<?php if($this->get('info') != ''){ ?>

<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>

<?php } ?>

<h1><?php echo $lang['NEW_TICKET']; ?></h1>


<form action="" method="post" enctype="multipart/form-data">

<fieldset><legend><?php echo $lang['TICKETDETAILS']; ?></legend>

<label for="headline"><?php echo $lang['HEADLINE']; ?></label> <br /><input
	type="text" name="headline" id="headline"
	value="<?php echo $values['headline']; ?>" /><br />
<label for="description"><?php echo $lang['DESCRIPTION']; ?></label><br /> <textarea
	rows="10" cols="30" name="description" id="description"><?php echo $values['description']; ?></textarea><br />

<label for="project"><?php echo $lang['PROJECT']; ?></label><br /> <select
	id="project" name="project">
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

<label for="priority"><?php echo $lang['PRIORITY']; ?></label><br /> <select
	id="prio" name="priority">

	<?php
	foreach($objTickets->priority as $key => $value) {
		echo'<option value="'.$key.'"';
		if($key == $values['priority']) echo'selected="selected"';
		echo'>'.$lang[$value].'</option>';
	}
	?>

</select><br />

<label for="dateToFinish"><?php echo $lang['DATE_TO_FINISH']; ?><small
	class="grey"><br />
(dd.mm.yyyy)</small></label><br /><br /> <input type="text" name="dateToFinish"
	id="dateToFinish" value="<?php echo $values['dateToFinish']; ?>" /><br />

	<?php if($this->get('role') !== 'client'){ ?> <br />
<label for="editFrom"><?php echo $lang['EDIT_FROM']; ?></label><br /> <input
	type="text" name="editFrom" id="editFrom"
	value="<?php echo $values['editFrom']; ?>" /><br />
<label for="editTo"><?php echo $lang['EDIT_TO']; ?></label> <br /><input
	type="text" name="editTo" id="editTo"
	value="<?php echo $values['editTo']; ?>" /><br />

<br />
<label for="editorId"><?php echo $lang['EMPLOYEE']; ?></label> <br /><select
	id="editorId" name="editorId">
	<option value=""><?php echo $lang['NO_EDITOR']; ?></option>
	<?php foreach($this->get('employees') as $row){
		echo'<option value="'.$row['id'].'" ';
		if($values['userId'] == $row['id'])echo'selected="selected"';
		echo'>'.$row['lastname'].', '.$row['firstname'].'</option>';
	}?>

</select><br />

	<?php } ?> 
</fieldset>

<fieldset><legend><?php echo $lang['TECHNICAL_DETAILS']; ?></legend> <label
	for="os"><?php echo $lang['OPERATING_SYSTEM']; ?></label><br /> <select
	name="os" id="os">

	<?php foreach($objTickets->os as $row)
	{
		echo'<option value="'.$row.'" ';
		if($values['os'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	}
	?>

</select><br />

<label for="browser"><?php echo $lang['BROWSER']; ?></label><br /> <select
	name="browser" id="browser">
	<?php
	foreach($objTickets->browser as $row)
	{
		echo'<option value="'.$row.'" ';
		if($values['browser'] == $row){
			echo'selected="selected"';
		}
		echo'> '.$lang[$row].'</option>';
	}
	?>

</select><br />

<label for="resolution"><?php echo $lang['RESOLUTION']; ?></label><br /> <select
	name="resolution" id="resolution">

	<?php foreach($objTickets->res as $row)
	{
		echo'<option value="'.$row.'" ';
		if($values['resolution'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	}
	?>

</select><br />

<label for="version"><?php echo $lang['VERSION']; ?></label> <br /><input
	type="text" name="version" id="version"
	value="<?php echo $values['version']; ?>" /><br />

<label for="url"><?php echo $lang['URL']; ?></label><br /> <input type="text"
	name="url" id="url" value="<?php echo $values['url']; ?>" /><br />

</fieldset>

<fieldset><legend><?php echo $lang['ATTACHMENTS']; ?></legend> <label
	for="file"><?php echo $lang['UPLOAD']; ?>:</label> <input name="file"
	id="file" type="file" /><br />
<hr />
<br />
</fieldset>

<fieldset>
<legend><?php echo $lang['SAVE']; ?></legend>
<input type="submit" name="save" id="save"
	value="<?php echo $lang['SAVE']; ?>" class="button" />
</fieldset>
</form>
<a href="index.php?act=tickets.showAll" class="link"><?php echo $lang['BACK']; ?></a>

