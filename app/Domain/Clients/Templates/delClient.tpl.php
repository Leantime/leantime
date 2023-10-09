<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$client = $tpl->get('client');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo sprintf($tpl->__('headline.delete_client'), $client['name']); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification() ?>

        <h4 class="widget widgettitle"><?php echo $tpl->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">

            <form method="post">

                <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

                <p><?php echo $tpl->__('text.confirm_client_deletion'); ?><br /></p>
                <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="/clients/showClient/<?php echo $client['id'] ?>"><?php echo $tpl->__('buttons.back'); ?></a>

                <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

            </form>
        </div>

    </div>
</div>
