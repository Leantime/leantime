<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
?>

<h1>Kalender löschen</h1>

<?php if ($tpl->get('msg') === '') { ?>
<form method="post" accept-charset="utf-8">
<fieldset><legend><?php echo $tpl->__('CONFIRM_DELETE'); ?></legend>
<p>Soll der Kalender wirklich gelöscht werden?<br />
</p>
<input type="submit" value="<?php echo $tpl->__('DELETE'); ?>" name="del"
    class="button"></fieldset>
</form>

<?php } else { ?>
<span class="info"><?php echo $tpl->get('msg'); ?></span>

<?php } ?>
