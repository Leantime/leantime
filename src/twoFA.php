<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontController->includeAction('general.header'); ?>
</head>

<script type="text/javascript">
    jQuery(document).ready(function () {

        if (jQuery('.login-alert .alert').text() != '') {
            jQuery('.login-alert').fadeIn();
        }

    });
</script>

<?php

    $redirectUrl = "/dashboard/show";

if (isset($_GET['redirectUrl'])) {
    $redirectUrl = filter_var($_GET['redirectUrl'], FILTER_SANITIZE_URL);
}

?>

<body class="loginpage" style="height:100%;">

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">
        <div class="row">
            <div class="col-md-12" style="position:relative;">
                <h1 class="mainWelcome"><?php echo $language->__("headlines.welcome_back"); ?></h1>
                <span class="iq-objects-04 iq-fadebounce">
                    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight">

        <div class="regpanel">
            <div class="regpanelinner">
                <div class="pageheader">
                    <div class="pagetitle">
                        <h1><?php echo $this->language->__("headlines.twoFA_login"); ?></h1>
                    </div>
                </div>
                <div class="regcontent">
                    <form id="login" action="<?php echo BASE_URL . "/index.php?twoFA=1&redirectUrl=$redirectUrl" ?>" method="post">
                        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl ?>"/>
                        <div class="inputwrapper login-alert">
                            <div class="alert alert-error"><?php echo $login->error; ?></div>
                        </div>
                        <div class="">
                            <input type="text" name="twoFA_code" id="twoFA_code" class="form-control"
                                   placeholder="<?php echo $this->language->__("label.twoFACode"); ?>"
                                   value=""/>
                        </div>
                        <div class="">
                            <div class="forgotPwContainer">
                                <a href="<?=BASE_URL ?>/index.php?logout=1" class="forgotPw"><?php echo $this->language->__("menu.sign_out"); ?></a>
                            </div>
                            <input type="submit" name="login" value="<?php echo $this->language->__("buttons.login"); ?>"
                                   class="btn btn-primary"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
