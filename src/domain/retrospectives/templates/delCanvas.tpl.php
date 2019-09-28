<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<h4 class="widgettitle title-light"><i class="iconfa iconfa-trash"></i> Delete</h4>

<form method="post" action="/leancanvas/delCanvas/<?php echo $_GET['id']?>">
    <p><?php echo $lang['CONFIRM_DELETE_CANVAS_ITEM']; ?></p><br />
    <input type="submit" value="Yes, delete!" name="del" class="button" />
    <a class="btn btn-secondary" href="/leancanvas/showCanvas/"><?php echo $lang['BACK']; ?></a>
</form>
