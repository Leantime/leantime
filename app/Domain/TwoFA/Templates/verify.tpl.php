<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$redirectUrl = $tpl->get("redirectUrl");
?>

<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__("headlines.twoFA_login"); ?></h1>
    </div>
</div>
<div class="regcontent">
    <form id="login" action="<?php echo BASE_URL . "/twoFA/verify" ?>" method="post">
        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl ?>"/>

        <?php echo $tpl->displayInlineNotification(); ?>

        <div class="">
            <input type="text" name="twoFA_code" id="twoFA_code" class="form-control"
                   placeholder="<?php echo $tpl->language->__("label.twoFACode"); ?>"
                   value="" autofocus/>
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="<?=BASE_URL ?>/auth/logout" class="forgotPw"><?php echo $tpl->language->__("menu.sign_out"); ?></a>
            </div>
            <input type="submit" name="login" value="<?php echo $tpl->language->__("buttons.login"); ?>"
                   class="btn btn-primary"/>
        </div>
    </form>
</div>
