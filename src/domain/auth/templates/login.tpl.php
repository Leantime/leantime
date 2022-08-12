<?php
    $redirectUrl = $this->get('redirectUrl');
?>

<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $this->language->__("headlines.login"); ?></h1>
    </div>
</div>

<div class="regcontent">
    <form id="login" action="<?=BASE_URL."/auth/login"?>" method="post">
        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl; ?>" />

        <?php echo $this->displayInlineNotification(); ?>

        <div class="">
            <input type="text" name="username" id="username" class="form-control" placeholder="<?php echo $this->language->__("input.placeholders.enter_email"); ?>" value=""/>
        </div>
        <div class="">
            <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo $this->language->__("input.placeholders.enter_password"); ?>" value=""/>
            <div class="forgotPwContainer">
                <a href="<?=BASE_URL ?>/auth/resetPw" class="forgotPw"><?php echo $this->language->__("links.forgot_password"); ?></a>
            </div>
        </div>
        <div class="">
            <input type="submit" name="login" value="<?php echo $this->language->__("buttons.login"); ?>" class="btn btn-primary"/>
        </div>
        <div>
        </div>

    </form>
</div>