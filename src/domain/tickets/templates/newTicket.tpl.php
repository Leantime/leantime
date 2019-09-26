<?php
defined('RESTRICTED') or die('Restricted access');

$projects = $this->get('projects');
$values = $this->get('values');
$tickets = $this->get('objTicket');
$type = $this->get('type');

?>

<script type="text/javascript">
    jQuery(function () {
        jQuery('.tabbedwidget').tabs();

    });
</script>

<style type='text/css'>
    .stdform label {
        width: 105px !important;
    }

</style>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?php echo $_SESSION['lastPage'] ?>" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> Go Back</a>
    </div>

    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1>New To-Do</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php
            echo $this->displayNotification();
        ?>

        <div class="tabbedwidget tab-primary">

            <ul>
                <li><a href="#ticketdetails"><?php echo $this->displaySubmoduleTitle('tickets-ticketDetails') ?></a>
                </li>
            </ul>

            <div id="ticketdetails">
                <?php $this->displaySubmodule('tickets-ticketDetails') ?>
            </div>

        </div>
    </div>
</div>

