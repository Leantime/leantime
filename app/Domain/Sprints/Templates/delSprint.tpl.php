<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<h4 class="widgettitle title-light"><?= $tpl->__('headlines.delete_sprint') ?></h4>

<form method="post" action="<?= BASE_URL ?>/sprints/delSprint/<?php echo $tpl->get('id') ?>">
    <p><?= $tpl->__('text.are_you_sure_delete_sprint') ?></p><br />
    <input type="submit" value="<?= $tpl->__('buttons.yes_delete') ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?php echo session('lastPage') ?>"><?= $tpl->__('buttons.back') ?></a>
</form>

