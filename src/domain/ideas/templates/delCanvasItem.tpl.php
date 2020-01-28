<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<h4 class="widgettitle title-light"><i class="iconfa iconfa-trash"></i> <?php echo $this->__("buttons.delete") ?></h4>

<form method="post" action="/ideas/delCanvasItem/<?php echo $_GET['id']?>">
    <p><?php echo $this->__("text.are_you_sure_delete_retro") ?></p><br />
    <input type="submit" value="<?php echo $this->__("buttons.yes_delete")?>" name="del" class="button" />
    <a class="btn btn-secondary" href="/ideas/showBoards/"><?php echo $this->__("buttons.back") ?></a>
</form>
