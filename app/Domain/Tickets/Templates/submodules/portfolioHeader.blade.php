<?php

use Leantime\Core\Controller\Frontcontroller;

$currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

$clients = $tpl->get('clients');
$currentClient = $tpl->get("currentClient");
$currentClientName = $tpl->get("currentClientName");

?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon">
        <span class="fa fa fa-briefcase"></span>
    </div>
    <div class="pagetitle">

        <h1><?php echo $tpl->__("headlines.my_projects"); ?>



        </h1>

    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
