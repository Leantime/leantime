<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> <?php echo $this->__("buttons.delete") ?></h4>

<form method="post" action="<?=BASE_URL ?>/ideas/delCanvasItem/<?php echo (int)$_GET['id']?>">
    <p><?php echo $this->__("text.are_you_sure_delete_idea") ?></p><br />
    <input type="submit" value="<?php echo $this->__("buttons.yes_delete")?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?=BASE_URL ?>/ideas/showBoards/"><?php echo $this->__("buttons.back") ?></a>
</form>
