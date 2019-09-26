<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h1>Termin löschen</h1>

<?php if($this->get('msg') === '') { ?>

<form action="" method="post">
<fieldset><legend>Löschen bestätigen</legend>
<p>Wollen sie den Termin wirklich löschen?<br />
</p>
<input type="submit" value="Löschen" name="del"
    class="button"></fieldset>
</form>

<?php }else{ ?>
<span class="info"><?php echo $this->get('msg'); ?></span>
<?php } ?>

<a href="index.php?act=calendar.showMyCalendar" class="link">zurück</a>
