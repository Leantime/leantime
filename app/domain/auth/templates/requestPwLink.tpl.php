<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $this->language->__("headlines.reset_password"); ?></h1>
    </div>
</div>
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $this->dispatchTplEvent('afterRegcontentOpen'); ?>
    <form id="resetPassword" action="" method="post">
        <?php $this->dispatchTplEvent('afterFormOpen'); ?>
        <?php echo $this->displayInlineNotification(); ?>
        <p><?php echo $this->language->__("text.enter_email_address_to_reset"); ?><br /><br /></p>
        <div class="">
            <input type="text" name="username" id="username" placeholder="<?php echo $this->language->__("input.placeholders.enter_email"); ?>" />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="<?=BASE_URL ?>/" class="forgotPw"><?php echo $this->language->__("links.back_to_login"); ?></a>
            </div>
            <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>
            <input type="submit" name="resetPassword" value="<?php echo $this->language->__("buttons.reset_password"); ?>" />
        </div>
        <?php $this->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $this->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>
