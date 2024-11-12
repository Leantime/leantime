<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> <?php echo $tpl->__('buttons.delete') ?></h4>

<form method="post" action="<?= BASE_URL ?>/wiki/delWiki/<?php echo $_GET['id']?>">
    <p><?php echo $tpl->__('text.are_you_sure_delete_wiki') ?></p>
    <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete')?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/wiki/show"><?php echo $tpl->__('buttons.back') ?></a>
</form>



