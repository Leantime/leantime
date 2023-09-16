<?php
    defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
    $ticket = $tpl->get("ticket");
?>

<div class="pageheader">

    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']; ?></h5>
        <h1><?php echo $tpl->__('headline.delete_ticket'); ?>: <?= $tpl->e($ticket->headline);?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <h4 class="widget widgettitle"><?php echo $tpl->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">
            <form method="post" action="<?=BASE_URL ?>/tickets/delTicket/<?php echo $ticket->id ?>">
                <p><?php echo $tpl->__('text.confirm_ticket_deletion'); ?></p><br />
                <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="#/tickets/showTicket/<?php echo $ticket->id ?>"><?php echo $tpl->__('buttons.back'); ?></a>
            </form>
        </div>
    </div>
</div>
