<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get("ticket");
?>

<h4 class="widgettitle title-light"><?php echo $this->__("subtitles.delete_milestone") ?></h4>

<form method="post" action="<?=BASE_URL ?>/tickets/delMilestone/<?php echo $ticket->id ?>">
    <p><?php echo $this->__('text.confirm_milestone_deletion'); ?></p><br />
    <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?=BASE_URL ?>/tickets/roadmap/"><?php echo $this->__('buttons.back'); ?></a>
</form>

