<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$user = $tpl->get('user');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><h1><?php echo $tpl->__('headlines.delete_user'); ?></h1></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification() ?>

        <h4 class="widget widgettitle"><?php echo $tpl->__('subtitles.delete'); ?></h4>
        <div class="widgetcontent">

            <form method="post">
                <input type="hidden" name="<?= session('formTokenName')?>" value="<?= session('formTokenValue')?>" />
                <p><?php echo $tpl->__('text.confirm_user_deletion'); ?></p><br />
                <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="<?= BASE_URL ?>/users/showAll"><?php echo $tpl->__('buttons.back'); ?></a>
            </form>


        </div>
    </div>
</div>
