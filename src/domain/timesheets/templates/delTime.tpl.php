<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h1><?php printf("".$lang['DELETE_TIME'].""); ?></h1>

<?php if($this->get('msg') === '') { ?>

<form method="post" accept-charset="utf-8">
<fieldset><legend><?php echo $lang['CONFIRM_DELETE']; ?></legend>
<p><?php echo $lang['CONFIRM_DELETE_QUE']; ?><br />
</p>
<input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del"
    class="button"></fieldset>
</form>

<?php }else{ ?>

<span class="info"><?php echo $lang[$this->get('msg')]; ?></span>

<?php } ?>
