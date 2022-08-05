<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontController->includeAction('general.header'); ?>
</head>

<?php

    $redirectUrl = BASE_URL . "/dashboard/show";

if ($_SERVER['REQUEST_URI'] != '' && isset($_GET['logout']) === false) {
    $redirectUrl = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
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

                <a href="<?=BASE_URL ?>" target="_blank"><img src="<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>" /></a>

                <div class="pageheader">
                    <div class="pagetitle">
                        <h1><?php echo $this->language->__("headlines.login"); ?></h1>
                    </div>
                </div>
                <div class="regcontent">
                    <form id="login" action="<?php echo $redirectUrl?>" method="post">
                        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl; ?>" />
                        <div class="inputwrapper login-alert">
                            <div class="alert alert-error"><?php echo $login->error;?></div>
                        </div>
                        <div class="">
                            <input type="text" name="username" id="username" class="form-control" placeholder="<?php echo $this->language->__("input.placeholders.enter_email"); ?>" value=""/>
                        </div>
                        <div class="">
                            <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo $this->language->__("input.placeholders.enter_password"); ?>" value=""/>
                            <div class="forgotPwContainer">
                                <a href="<?=BASE_URL ?>/resetPassword" class="forgotPw"><?php echo $this->language->__("links.forgot_password"); ?></a>
                            </div>
                        </div>
                        <div class="">
                            <input type="submit" name="login" value="<?php echo $this->language->__("buttons.login"); ?>" class="btn btn-primary"/>
                        </div>
                        <div>
                              </div>

                    </form>
                </div>
            </div>
        </div>



    </div>
</div>

</body>
</html>
