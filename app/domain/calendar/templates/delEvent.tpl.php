<?php
    defined('RESTRICTED') or die('Restricted access');
    foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
?>

<h4 class="widgettitle title-light"><?php echo $tpl->__("subtitles.delete") ?></h4>

<form method="post" class="formModal">
    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
    <p><?php echo $tpl->__('text.confirm_event_deletion'); ?></p><br />
    <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
    <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
    <a class="btn btn-primary" href="<?=BASE_URL ?>/calendar/showMyCalendar"><?php echo $tpl->__('buttons.back'); ?></a>
    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
</form>

