<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$tpl->dispatchTplEvent('beforePageHeaderOpen');
?>
<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__('headlines.reset_password'); ?></h1>
    </div>
</div>
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $tpl->dispatchTplEvent('afterRegcontentOpen'); ?>
    <form id="resetPassword" action="" method="post">
        <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
        <?php echo $tpl->displayInlineNotification(); ?>
        <p><?php echo $tpl->language->__('text.enter_email_address_to_reset'); ?><br /><br /></p>
        <div class="">
            <input type="text" name="username" id="username" placeholder="<?php echo $tpl->language->__('input.placeholders.enter_email'); ?>" />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="<?= BASE_URL ?>/" class="forgotPw"><?php echo $tpl->language->__('links.back_to_login'); ?></a>
            </div>
            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
            <input type="submit" name="resetPassword" value="<?php echo $tpl->language->__('buttons.reset_password'); ?>" />
        </div>
        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $tpl->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>
