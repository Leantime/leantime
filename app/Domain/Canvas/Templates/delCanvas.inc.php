<?php

/**
 * delCanvas.inc template - Generic template for deleting canvas
 *
 * Required variables:
 * - $canvasName     Name of current canvas
 */
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
?>


<h4 class="widgettitle title-light"><?= $tpl->__('subtitles.delete') ?></h4>


<form method="post" action="<?= BASE_URL ?>/<?= $canvasName ?>canvas/delCanvas/<?= $id ?>">
    <p><?php echo $tpl->__('text.confirm_board_deletion'); ?></p><br />
    <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
    <a class="btn btn-secondary"
       href="<?= BASE_URL ?>/<?= $canvasName ?>canvas/showCanvas"><?php echo $tpl->__('buttons.back'); ?></a>
</form>


