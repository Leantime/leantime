<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> <?php echo $this->__("buttons.delete") ?></h4>

<form method="post" action="<?=BASE_URL ?>/wiki/delArticle/<?php echo (int)$_GET['id']?>">
    <p><?php echo $this->__("text.are_you_sure_delete_article") ?></p><br />
    <input type="submit" value="<?php echo $this->__("buttons.yes_delete")?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?=BASE_URL ?>/wiki/show/"><?php echo $this->__("buttons.back") ?></a>
</form>
