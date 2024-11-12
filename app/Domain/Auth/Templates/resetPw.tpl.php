<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$tpl->dispatchTplEvent('beforePageHeaderOpen');
?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__('headlines.reset_password'); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div>
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $tpl->dispatchTplEvent('afterRegcontentOpen'); ?>
    <form id="resetPassword" action="" method="post">
        <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

        <?php echo $tpl->displayInlineNotification(); ?>

        <p><?php echo $tpl->language->__('text.enter_new_password'); ?><br /><br /></p>

        <div class="">
            <input type="password" name="password" id="password" placeholder="<?php echo $tpl->language->__('input.placeholders.enter_new_password'); ?>" />
            <span id="pwStrength" style="width:100%;"></span>
        </div>
        <div class=" ">
            <input type="password" name="password2" id="password2" placeholder="<?php echo $tpl->language->__('input.placeholders.confirm_password'); ?>" />
        </div>
        <small><?= $tpl->__('label.passwordRequirements') ?></small><br /><br />
        <div class="">

            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
            <input type="submit" name="resetPassword" value="<?php echo $tpl->language->__('buttons.reset_password'); ?>" />
            <div class="forgotPwContainer">
                <a href="<?= BASE_URL ?>/" class="forgotPw"><?php echo $tpl->language->__('links.back_to_login'); ?></a>
            </div>
        </div>
        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $tpl->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>

<script>
    leantime.usersController.checkPWStrength('password');
</script>
