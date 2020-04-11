<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h4 class="widgettitle title-light"><?php printf("".$this->__('headlines.delete_time').""); ?></h4>

<form method="post" action="<?=BASE_URL ?>/timesheets/delTime/<?php echo $this->get('id') ?>">
    <p><?=$this->__("text.confirm_delete_timesheet") ?></p><br />
    <input type="submit" value="<?=$this->__("buttons.yes_delete") ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?php echo $_SESSION['lastPage'] ?>"><?=$this->__("buttons.back") ?></a>
</form>

