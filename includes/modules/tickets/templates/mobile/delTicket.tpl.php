<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$ticket = $this->get('ticket');

?>

<h1><?php printf($lang['DELETE_TICKET'], $ticket['id']); ?></h1>

<?php if($this->get('info') === '') { ?>

<form method="post">
<fieldset><legend><?php echo $lang['CONFIRM_DELETE']; ?></legend>
<p><?php echo $lang['CONFIRM_DELETE_TICKET']; ?></p>
<br />
<input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del"
	class="button"></fieldset>
</form>
<?php }else{ ?>

<span class="info"><?php echo $lang[$this->get('info')] ?></span>

<?php } ?>
<a href="index.php?act=tickets.showAll" class="link"><?php echo $lang['BACK']; ?></a>
