<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<h4 class="widgettitle title-light"><?php printf("" . $tpl->__('headlines.delete_time') . ""); ?></h4>

<form method="post" action="<?=BASE_URL ?>/timesheets/delTime/<?php echo $tpl->get('id') ?>">
    <p><?=$tpl->__("text.confirm_delete_timesheet") ?></p><br />
    <input type="submit" value="<?=$tpl->__("buttons.yes_delete") ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?php echo session("lastPage") ?>"><?=$tpl->__("buttons.back") ?></a>
</form>

