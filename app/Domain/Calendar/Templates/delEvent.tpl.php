<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$id = (int) $_GET['id'];
?>

<h4 class="widgettitle title-light"><?php echo $tpl->__('subtitles.delete') ?></h4>

<form method="post" class="formModal" action="<?= BASE_URL ?>/calendar/delEvent/<?= $id?>">
    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
    <p><?php echo $tpl->__('text.confirm_event_deletion'); ?></p><br />
    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
    <button type="submit"  class="btn btn-primary" id="saveAndClose" value="closeModal"><?= $tpl->__('buttons.yes_delete') ?></button>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/calendar/showMyCalendar"><?php echo $tpl->__('buttons.back'); ?></a>
    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
</form>

