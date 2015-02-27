<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$client = $this->get('client');
?>

<h1><?php printf($lang['DELETE_CLIENT'], $client['name']); ?></h1>

<?php if($this->get('msg') === '') { ?>

<form action="" method="post">
<fieldset><legend><?php echo $lang['CONFIRM_DELETE']; ?></legend>
<p><?php echo $lang['CONFIRM_DELETE_QUE']; ?><br />
</p>
<input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del"
	class="button"></fieldset>
</form>

<?php }else{ ?>
<span class="info"><?php echo $lang[$this->get('msg')]; ?></span>
<?php } ?>

<a href="index.php?act=clients.showAll" class="link"><?php echo $lang['BACK']?></a>