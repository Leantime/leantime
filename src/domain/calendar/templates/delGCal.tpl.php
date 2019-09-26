<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h1>Kalender löschen</h1>

<?php if($this->get('msg') === '') { ?>

<form method="post" accept-charset="utf-8">
<fieldset><legend><?php echo $lang['CONFIRM_DELETE']; ?></legend>
<p>Soll der Kalender wirklich gelöscht werden?<br />
</p>
<input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del"
    class="button"></fieldset>
</form>

<?php }else{ ?>

<span class="info"><?php echo $this->get('msg'); ?></span>

<?php } ?>
