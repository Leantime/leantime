<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h1>Delete Event</h1>

<?php if($this->get('msg') === '') { ?>

<form action="" method="post">
<fieldset><legend>Confirm</legend>
<p>Are you sure you want to delete this event?<br />
</p>
<input type="submit" value="Delete" name="del"
    class="button"></fieldset>
</form>

<?php }else{ ?>
<span class="info"><?php echo $this->get('msg'); ?></span>
<?php } ?>
