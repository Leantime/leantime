<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$projects = $this->get('projects');
$objTickets = $this->get('objTickets');
$values = $this->get('values');
$helper = $this->get('helper');

?>

<script type="text/javascript">
	$(function() {
		
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
		cleanup : false,
		convert_urls : false,
		cleanup_on_startup: false,
		relative_urls: false,
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

<?php if($this->get('info') != ''){ ?>

<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>

<?php } ?>

<h1><?php echo $lang['EDIT_ARTICLE']; ?></h1>

<form method="post">
<fieldset><legend><?php echo $lang['TICKETDETAILS']; ?></legend>

<label for="headline"><?php echo $lang['HEADLINE']; ?></label> <input
	type="text" name="headline" id="headline"
	value="<?php echo $values['headline']; ?>" /><br />
<label for="text"><?php echo $lang['DESCRIPTION']; ?></label> <textarea
	rows="10" cols="70" name="text" id="text"><?php echo $values['text']; ?></textarea><br />



<label for="tags"><?php echo $lang['TAGS']; ?></label> <input
	type="text" name="tags" id="tags"
	value="<?php echo $values['tags']; ?>" /><br />
<br />



<label for="category"><?php echo $lang['CATEGORY']; ?></label>
<select id="category" name="category">

<?php foreach($this->get('categories') as $row) {?>
<option value="<?php echo $row['id']; ?>"
<?php if($values['category'] == $row['id']) echo 'selected="selected"'; ?>
><?php echo $row['name']; ?> </option>

<?php } ?>

</select><br />


<input type="submit" name="save" id="save"
	value="<?php echo $lang['SAVE']; ?>" class="button" />
	</fieldset>



</form>


<form action="" method="post" enctype="multipart/form-data">

<fieldset><legend><?php echo $lang['ATTACHMENTS']; ?></legend> <label
	for="file"><?php echo $lang['UPLOAD']; ?>:</label> <input name="file"
	id="file" type="file" /><br />
<input name="upload" id="upload" value="<?php echo $lang['UPLOAD']; ?>"
	type="submit" class="button" />
<hr />
<br />

	<?php
	$row = '';

	foreach($this->get('files') as $row){

		echo'<a href="userdata/'.$row['encName'].'" target="_blank">'.$row['realName'].'</a><br />';
		echo'<input type="text" value="userdata/'.$row['encName'].'" /><br />';
		printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $row['firstname'], $row['lastname'], $helper->timestamp2date($row['date'], 2));
		
		if($this->get('role') == 'admin'){
			echo' | <a href="index.php?act=tickets.showTicket&amp;id='.$values['id'].'&amp;delFile='.$row['encName'].'">'.$lang['DELETE'].'</a>';
		}

		echo'<br /><br />';

	}

	if(count($this->get('files')) == 0){
		echo''.$lang['ERROR_NO_FILES'].'';
	}
	?></fieldset>
</form>
