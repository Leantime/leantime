<?php
    $redirectUrl = $this->get('redirectUrl');
?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pagetitle">
        <h1><?php echo $this->language->__("headlines.login"); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div>
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="regcontent">
    <?php $this->dispatchTplEvent('afterRegcontentOpen'); ?>
    <form id="login" action="<?=BASE_URL . "/auth/login"?>" method="post">
        <?php $this->dispatchTplEvent('afterFormOpen'); ?>
        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl; ?>" />

        <?php echo $this->displayInlineNotification(); ?>

        <div class="">
            <input type="text" name="username" id="username" class="form-control" placeholder="<?php echo $this->language->__($this->get("inputPlaceholder")); ?>" value=""/>
        </div>
        <div class="">
            <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo $this->language->__("input.placeholders.enter_password"); ?>" value=""/>
            <div class="forgotPwContainer">
                <a href="<?=BASE_URL ?>/auth/resetPw" class="forgotPw"><?php echo $this->language->__("links.forgot_password"); ?></a>
            </div>
        </div>
        <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>
        <div class="">
            <input type="submit" name="login" value="<?php echo $this->language->__("buttons.login"); ?>" class="btn btn-primary"/>
        </div>
        <div>
        </div>
        <?php $this->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php if ($this->get('oidcEnabled')) { ?>
        <?php $this->dispatchTplEvent('beforeOidcButton'); ?>
        <div class="">
            <a href="<?=BASE_URL ?>/oidc/login" style="width:100%;" class="btn btn-primary">
            <?php echo $this->language->__("buttons.oidclogin"); ?>
            </a>
        </div>
    <?php } ?>
    <?php $this->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>
