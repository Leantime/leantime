<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$values = $this->get('values');
?>

<h1><?php echo $lang['NEW_CLIENT']; ?></h1>

<?php if($this->get('info') != ''){ ?>
<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>
<?php } ?>

<form action="" method="post">
<fieldset><legend><?php echo $lang['CLIENT_DETAILS']?></legend> <label
	for="name"><?php echo $lang['NAME']?></label><br /> <input type="text"
	name="name" id="name" value="<?php echo $values['name'] ?>" /><br />

<label for="street"><?php echo $lang['STREET']?></label><br /> <input
	type="text" name="street" id="street"
	value="<?php echo $values['street'] ?>" /><br />

<label for="zip"><?php echo $lang['ZIP']?></label><br /> <input type="text"
	name="zip" id="zip" value="<?php echo $values['zip'] ?>" /><br />

<label for="city"><?php echo $lang['CITY']?></label><br /> <input type="text"
	name="city" id="city" value="<?php echo $values['city'] ?>" /><br />

<label for="state"><?php echo $lang['STATE']?></label><br /> <input
	type="text" name="state" id="state"
	value="<?php echo $values['state'] ?>" /><br />

<label for="country"><?php echo $lang['COUNTRY']?></label><br /> <input
	type="text" name="country" id="country"
	value="<?php echo $values['country'] ?>" /><br />

<label for="phone"><?php echo $lang['PHONE']?></label><br /> <input
	type="text" name="phone" id="phone"
	value="<?php echo $values['phone'] ?>" /><br />

<label for="internet"><?php echo $lang['URL']?></label><br /> <input
	type="text" name="internet" id="internet"
	value="<?php echo $values['internet'] ?>" /><br />
<br />
<input type="submit" name="save" id="save"
	value="<?php echo $lang['SAVE']?>" class="button" />
	</fieldset>

</form>
<a href="index.php?act=clients.showAll" class="link"><?php echo $lang['BACK']?></a>