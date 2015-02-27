<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$catName = $this->get('catName');

?>

<h1><?php echo $lang['ADD_CATEGORY']; ?></h1>

<?php if($this->get('info') != '') { ?>

<span class="info"><?php echo $lang[$this->get('info')] ?></span>

<?php } ?>

<form method="post" action="">
<fieldset>
<label for="catName"><?php echo $lang['CATNAME']; ?></label><br />
<input type="text" id="catName" name="catName" value="<?php echo $this->get('catName'); ?>"/>
<br /><br />
<input type="submit" value="<?php echo $lang['SAVE']; ?>" name="save"
	class="button"></fieldset>
</form>
<a href="index.php?act=wiki.showAll" class="link"><?php echo $lang['BACK']; ?></a>