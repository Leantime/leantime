<?php
defined('RESTRICTED') or die('Restricted access');

$ticket = $this->get('ticket');

?>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?php echo $_SESSION['lastPage'] ?>" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> <?=$this->__("links.go_back") ?></a>
    </div>

    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1><?=$this->__("headlines.new_to_do") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php
            echo $this->displayNotification();
        ?>

        <div class="tabbedwidget tab-primary ticketTabs">

            <ul>
                <li>
                    <a href="#ticketdetails"><?php echo $this->__("tabs.ticketDetails") ?></a>
                </li>
            </ul>

            <div id="ticketdetails">
                <form class="ticketModal" action="/tickets/newTicket" method="post">
                    <?php $this->displaySubmodule('tickets-ticketDetails') ?>
                </form>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

    leantime.ticketsController.initTicketTabs();

    jQuery(window).load(function () {
        jQuery(window).resize();
    });

</script>