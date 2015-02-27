<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$project = $this->get('project');
?>

<h1><?php printf($lang['DELETE_PROJECT'], $project['name']); ?></h1>


<?php if($this->get('msg') === '') { ?>

<form method="post">
<fieldset><legend><?php echo $lang['CONFIRM_DELETE']; ?></legend>
<p><?php echo $lang['CONFIRM_DELETE_TEXT']; ?></p>
<br />
<input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del"
	class="button"></fieldset>
</form>

<?php }else{ ?>
<span class="info"><?php echo $lang[$this->get('msg')]; ?></span>
<?php } ?>

<a href="index.php?act=projects.showAll" class="link"><?php echo $lang['BACK'];?></a>