<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h1><?php printf("".$this->__('headlines.delete_time').""); ?></h1>

<?php if($this->get('msg') === '') { ?>

<form method="post" accept-charset="utf-8">
<fieldset><legend><?php echo $this->__('headlines.confirm_delete'); ?></legend>
<p><?php echo $this->__('text.confirm_delete_timesheet'); ?><br />
</p>
<input type="submit" value="<?php echo $this->__('buttons.delete') ?>" name="del"
    class="button"></fieldset>
</form>

<?php }else{ ?>

<span class="info"><?php echo $this->displayNotification(); ?></span>

<?php } ?>
