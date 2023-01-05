<?php
    $user = $this->get("user");
?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pagetitle">
        <h1><?php echo $this->language->__("headlines.create_account"); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div>
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $this->dispatchTplEvent('afterRegcontentOpen'); ?>
    <form id="resetPassword" action="" method="post">
        <?php $this->dispatchTplEvent('afterFormOpen'); ?>

        <?php echo $this->displayInlineNotification(); ?>

        <p><?php echo $this->language->__("text.welcome_to_leantime"); ?><br /><br /></p>

        <div class="">
            <input type="text" name="firstname" id="firstname" placeholder="<?php echo $this->language->__("input.placeholders.firstname"); ?>" value="<?=$this->escape($user['firstname']); ?>" />

        </div>
        <div class="">
            <input type="text" name="lastname" id="lastname" placeholder="<?php echo $this->language->__("input.placeholders.lastname"); ?>" value="<?=$this->escape($user['lastname']); ?>" />

        </div>
        <div class="">
            <input type="password" name="password" id="password" placeholder="<?php echo $this->language->__("input.placeholders.enter_new_password"); ?>" />
            <span id="pwStrength" style="width:100%;"></span>
        </div>
        <div class=" ">
            <input type="password" name="password2" id="password2" placeholder="<?php echo $this->language->__("input.placeholders.confirm_password"); ?>" />
        </div>
        <small><?=$this->__('label.passwordRequirements') ?></small><br /><br />
        <div class="">
            <input type="hidden" name="saveAccount" value="1" />
            <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>
            <input type="submit" name="createAccount" value="<?php echo $this->language->__("buttons.create_account"); ?>" />

        </div>
        <?php $this->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $this->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>

<script>
    leantime.usersController.checkPWStrength('password');
</script>
