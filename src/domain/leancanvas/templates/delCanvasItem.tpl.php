<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<h4 class="widgettitle title-light"><?=$this->__("subtitles.delete") ?></h4>

<form method="post" action="<?=BASE_URL ?>/leancanvas/delCanvasItem/<?php echo $_GET['id']?>">
    <p><?php echo $this->__('text.confirm_research_board_item_deletion'); ?></p><br />
    <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?=BASE_URL ?>/leancanvas/simpleCanvas"><?php echo $this->__('buttons.back'); ?></a>
</form>
