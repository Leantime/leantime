<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );

$values = $this->get('values');
?>

<h1><?php echo $lang['NEW_PROJECT']; ?></h1>

<?php if($this->get('info') != ''){ ?>
<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>
<?php } ?>

<form action="" method="post">

<fieldset><legend><?php echo $lang['PROJECT_DETAILS']; ?></legend> <label
	for="name"><?php echo $lang['NAME']; ?></label> <br /><input type="text"
	name="name" id="name" value="<?php echo $values['name'] ?>" /><br />

<label for="details"><?php echo $lang['PROJECT_DETAILS']; ?></label> <br /><textarea
	name="details" id="details" rows="5" cols="30"><?php echo $values['details'] ?></textarea><br />

<label for="clientId"><?php echo $lang['CLIENT']; ?></label><br /> <select
	name="clientId" id="clientId">

	<?php foreach($this->get('clients') as $row){ ?>

	<option value="<?php echo $row['id']; ?>"
	<?php if($values['clientId'] == $row['id']){ ?> selected=selected
	<?php } ?>><?php echo $row['name']; ?></option>
	<?php } ?>
	<?php if(empty($row) === true) {?>
	<option value=""></option>
	<?php } ?>
</select> <br /><br />

<input type="submit" name="save" id="save"
	value="<?php echo $lang['SUBMIT']; ?>" class="button" /></fieldset>

</form>
<a href="index.php?act=projects.showAll" class="link"><?php echo $lang['BACK'];?></a>