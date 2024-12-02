<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get("ticket");
$error = $tpl->get("error");
?>


<h4 class="widgettitle title-light"><?php echo $tpl->__("subtitles.delete") ?></h4>

<?php if(!empty($error)) { ?>
    <?=$error ?>
<?php }else{ ?>

    <?php if (is_object($ticket)) { ?>
        <form method="post" action="<?=BASE_URL ?>/tickets/delTicket/<?=$ticket->id?>">
            <p><?php echo $tpl->__('text.confirm_ticket_deletion'); ?></p><br />
            <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />

            <a class="btn btn-primary" href="#/tickets/showTicket/<?php echo $ticket->id ?>"><?php echo $tpl->__('buttons.back'); ?></a>


        </form>

    <?php } else { ?>
        <p>Ticket not found</p>
    <?php } ?>
<?php } ?>
