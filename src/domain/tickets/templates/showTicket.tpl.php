<?php

defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');
$objTicket = $this->get('objTicket');
$helper = $this->get('helper');
$state = $this->get('state');
$statePlain = $this->get('statePlain');
$userId = $this->get('userId');
$unreadCount = $this->get('unreadCount');
$tickets = $objTicket;
?>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?php echo $_SESSION['lastPage'] ?>" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> Go Back</a>
    </div>

    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1>Edit ToDo</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">


        <script type="text/javascript">
            function changeStatus(id) {
                var state = new Array('label-success', 'label-warning', 'label-info', 'label-important', 'label-inverse');

                var statePlain = new Array('Finished', 'Problem', 'Unapproved', 'New', 'Seen');

                var newStatus = jQuery("#status-select-" + id + " option:selected").val();

                jQuery.ajax({
                    url: leantime.appUrl+'/index.php?act=general.ajaxRequest&module=tickets.showAll&export=true',
                    type: 'post',
                    data: {ticketId: id, newStatus: newStatus},
                    success: function (msg) {

                        jQuery("#status-" + id).show();

                        jQuery("#status-" + id).attr("class", "f-left " + state[newStatus]);
                        jQuery("#status-" + id).html(statePlain[newStatus]);

                        jQuery("#status-spinner-" + id).show();

                        jQuery("#status-select-" + id).hide();

                        jQuery(".maincontentinner").prepend("<div class='alert alert-success'><button data-dismiss='alert' class='close' type='button'>×</button>" + msg + "</div>");
                    }
                });

            }


            jQuery(document).ready(function () {

                    jQuery("#ticketCount").html(<?php echo $unreadCount; ?>);
                    jQuery('.tabbedwidget').tabs();

                }
            );


            jQuery(window).load(function () {
                jQuery(window).resize();
            });


        </script>

        <?php echo $this->displayNotification(); ?>


            <div class="tabbedwidget tab-primary">

            <ul>
                <li><a href="#ticketdetails"><?php echo $this->displaySubmoduleTitle('tickets-ticketDetails') ?></a>
                </li>
                <li><a href="#subtasks"><?php echo $this->displaySubmoduleTitle('tickets-subTasks') ?>
                        (<?php echo $this->get('numSubTasks'); ?>)</a></li>
                <li><a href="#files"><?php echo $this->displaySubmoduleTitle('tickets-attachments') ?>
                        (<?php echo $this->get('numFiles'); ?>)</a></li>
                <li><a href="#comments">Discussion
                        (<?php echo $this->get('numComments'); ?>)</a></li>

                <?php if ($this->displaySubmoduleTitle('tickets-timesheet') != '') : ?>
                    <li><a href="#timesheet">Time Tracking</a></li>
                <?php endif; ?>

            </ul>

            <div id="ticketdetails">
                <form class="ticketModal" action="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket['id']?>" method="post">
                    <?php $this->displaySubmodule('tickets-ticketDetails') ?>


                </form>
            </div>

            <div id="subtasks">
                <form method="post" action="#subtasks" class="ticketModal">
                    <?php $this->displaySubmodule('tickets-subTasks') ?>
                </form>
            </div>

            <div id="files">
                <form action='#files' method='POST' enctype="multipart/form-data" class="ticketModal">
                    <?php $this->displaySubmodule('tickets-attachments') ?>
                </form>
            </div>

            <div id="comments">
                <form method="post" action="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket['id']; ?>#comments" class="ticketModal">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $this->assign('formUrl', "/tickets/showTicket/".$ticket['id']."");
                    $this->displaySubmodule('comments-generalComment') ?>
                </form>
            </div>

            <?php if ($this->displaySubmoduleTitle('tickets-timesheet') != '') : ?>
                <div id="timesheet">
                    <?php $this->displaySubmodule('tickets-timesheet') ?>
                </div>
            <?php endif; ?>
        </div>

        <br/><br/>

    </div>
</div>
