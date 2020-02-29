<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<h4 class="widgettitle title-light"><i class="iconfa iconfa-trash"></i> Delete</h4>

<form method="post" action="<?=BASE_URL ?>/retrospectives/delCanvasItem/<?php echo $_GET['id']?>">
    <p>Are you sure you would like to delete this retrospective?</p><br />
    <input type="submit" value="Yes, delete!" name="del" class="button" />
    <a class="btn btn-secondary" href="<?=BASE_URL ?>/retrospectives/showBoards/">Back</a>
</form>
