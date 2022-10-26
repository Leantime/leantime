<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h4 class="widgettitle title-light"><?=$this->__("headlines.delete_sprint") ?></h4>

<form method="post" action="<?=BASE_URL ?>/sprints/delSprint/<?php echo $this->get('id') ?>">
    <p><?=$this->__("text.are_you_sure_delete_sprint") ?></p><br />
    <input type="submit" value="<?=$this->__("buttons.yes_delete") ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?php echo $_SESSION['lastPage'] ?>"><?=$this->__("buttons.back") ?></a>
</form>
                        
