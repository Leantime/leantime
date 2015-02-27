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


<?php if($this->get('info') != ''){ ?>

<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>

<?php } ?>

<h1><?php echo $lang['NEW_ARTICLE']; ?></h1>

<form method="post" enctype="multipart/form-data">
<fieldset><legend><?php echo $lang['TICKETDETAILS']; ?></legend>

<label for="headline"><?php echo $lang['HEADLINE']; ?></label><br /> <input
	type="text" name="headline" id="headline"
	value="<?php echo $values['headline']; ?>" /><br />
<label for="text"><?php echo $lang['DESCRIPTION']; ?></label> <br /><textarea
	rows="20" cols="30" name="text" id="text"><?php echo $values['text']; ?></textarea><br />



<label for="tags"><?php echo $lang['TAGS']; ?></label> <br /><input
	type="text" name="tags" id="tags"
	value="<?php echo $values['tags']; ?>" /><br />
<br />

<label for="category"><?php echo $lang['CATEGORY']; ?></label><br />
<select id="category" name="category">

<?php foreach($this->get('categories') as $row) {?>
<option value="<?php echo $row['id']; ?>"
<?php if($values['category'] == $row['id']) echo 'selected="selected"'; ?>
><?php echo $row['name']; ?> </option>

<?php } ?>

</select><br />



<br />
<input type="submit" name="save" id="save"
	value="<?php echo $lang['SAVE']; ?>" class="button" />
	</fieldset>




<fieldset><legend><?php echo $lang['ATTACHMENTS']; ?></legend> <label
	for="file"><?php echo $lang['UPLOAD']; ?>:</label> <input name="file"
	id="file" type="file" /><br />

<hr />
<br />

	<?php
	$row = '';

	foreach($this->get('files') as $row){

		echo'<a href="userdata/'.$row['encName'].'" target="_blank">'.$row['realName'].'</a><br />';
		echo'<input type="text" value="userdata/'.$row['encName'].'" /><br />';
		printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $row['firstname'], $row['lastname'], $helper->timestamp2date($row['date'], 2));
		
		if($this->get('role') == 'admin'){
			echo'<br /><a href="index.php?act=tickets.showTicket&amp;id='.$values['id'].'&amp;delFile='.$row['encName'].'">'.$lang['DELETE'].'</a>';
		}

		echo'<br /><br />';

	}

	if(count($this->get('files')) == 0){
		echo''.$lang['ERROR_NO_FILES'].'';
	}
	?></fieldset>
</form>
<a href="index.php?act=wiki.showAll" class="link"><?php echo $lang['BACK']; ?></a>