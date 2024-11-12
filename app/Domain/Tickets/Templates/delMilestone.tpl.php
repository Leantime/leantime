<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
?>

<h4 class="widgettitle title-light"><?php echo $tpl->__('subtitles.delete_milestone') ?></h4>

<form method="post" action="<?= BASE_URL ?>/tickets/delMilestone/<?php echo $ticket->id ?>">
    <p><?php echo $tpl->__('text.confirm_milestone_deletion'); ?></p><br />
    <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/tickets/roadmap/"><?php echo $tpl->__('buttons.back'); ?></a>
</form>

