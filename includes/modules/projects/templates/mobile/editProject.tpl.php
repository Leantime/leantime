<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );

$values = $this->get('values');
$helper = $this->get('helper');
?>

<h1><?php printf($lang['EDIT_PROJECT'], $values['name']);?></h1>

<?php if($this->get('info') != ''){ ?>
<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>
<?php } ?>


<form action="" method="post">
<fieldset ><legend><?php echo $lang['PROJECT_DETAILS']; ?></legend>

<label for="name"><?php echo $lang['NAME']; ?></label> <br /><input
	type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br />

<label for="details"><?php echo $lang['PROJECT_DETAILS']; ?></label><br /> <textarea
	name="details" id="details" rows="5" cols="30"><?php echo $values['details'] ?></textarea><br />

<label for="clientId"><?php echo $lang['CLIENT']; ?></label> <br /><select
	name="clientId" id="clientId">

	<?php foreach($this->get('clients') as $row){ ?>
	<option value="<?php echo $row['id']; ?>"
	<?php if($values['clientId'] == $row['id']){ ?> selected=selected
	<?php } ?>><?php echo $row['name']; ?></option>
	<?php } ?>

</select> <br />

<label for="projectState"><?php echo $lang['PROJECTAPPROVAL']; ?></label><br />

<select name="projectState" id="projectState">
	<option value="0" <?php if($values['state'] == 0){ ?> selected=selected
	<?php } ?>><?php echo $lang['OPEN']; ?></option>

	<option value="1" <?php if($values['state'] == 1){ ?> selected=selected
	<?php } ?>><?php echo $lang['CLOSED']; ?></option>

</select> <br /><br />

<input type="submit" name="save" id="save" class="button"
	value="<?php echo $lang['SAVE']; ?>" class="button" /></fieldset>

<fieldset><legend><?php echo $lang['ACCOUNTS']; ?></legend> <strong><?php echo $lang['ADD_ACCOUNT']; ?></strong><br />
<br />
<label for="accountName"><?php echo $lang['LABEL']; ?></label><br /> <input
	type="text" name="accountName" id="accountName" value="" /><br />

<label for="kind"><?php echo $lang['ACCOUNT_KIND']; ?></label><br /> <input
	type="text" name="kind" id="kind" value="" /><br />

<label for="username"><?php echo $lang['USERNAME']; ?></label><br /> <input
	type="text" name="username" id="username" value="" /><br />

<label for="password"><?php echo $lang['PASSWORD']; ?></label> <br /><input
	type="text" name="password" value="" /><br />

<label for="host"><?php echo $lang['HOST']; ?></label> <br /><input
	type="text" id="host" name="host" value="" /><br /><br />
<input type="submit" name="accountSubmit" class="button"
	value="<?php echo $lang['SUBMIT']; ?>" /></fieldset>

</form>

<form action="" method="post" enctype="multipart/form-data">

<fieldset><legend><?php echo $lang['ATTACHMENTS']; ?></legend> <label
	for="file"><?php echo $lang['FILE'];?> :</label><br /> <input name="file"
	id="file" type="file" /><br /><br />
<input name="upload" id="upload" class="button"
	value="<?php echo $lang['UPLOAD']; ?>" type="submit" />
<hr />
<br />

	<?php

	$row = '';

	foreach($this->get('files') as $rowFiles){?> <a
	href="userdata/<?php echo $rowFiles['encName']; ?>" target="_blank"><?php echo $rowFiles['realName']; ?></a><br />
	<?php printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $rowFiles['firstname'], $rowFiles['lastname'], $helper->timestamp2date($rowFiles['date'], 2)); ?>

<br />
<br />
	<?php } ?> <?php if(empty($rowFiles)){ ?> <?php echo $lang['ERROR_NO_FILES'];?>
	<?php } ?></fieldset>
</form>

<a href="index.php?act=projects.showAll" class="link"><?php echo $lang['BACK'];?></a>
