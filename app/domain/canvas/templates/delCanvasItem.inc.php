<?php

/**
 * delCanvasItem.inc template - Generic template for deleting canvas item
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 */

defined('RESTRICTED') or die('Restricted access');

$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
?>

<h4 class="widgettitle title-light"><?=$this->__("subtitles.delete") ?></h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<form method="post" action="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?=$id ?>">
    <p><?php echo $this->__('text.confirm_board_item_deletion'); ?></p><br />
    <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas"><?php echo $this->__('buttons.back'); ?></a>
</form>
