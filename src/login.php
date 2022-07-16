<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontController->includeAction('general.header'); ?>

    <link rel="stylesheet" href="<?=BASE_URL?>/css/vars.css.php?color=<?php echo htmlentities($_SESSION["companysettings.mainColor"]) ?>&v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/main.css?v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/overwrites.css" type="text/css"/>

    <script src="<?=BASE_URL?>/api/i18n"></script>

    <!-- libs -->
    <script src="<?=BASE_URL?>/js/compiled-base-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>
    <script src="<?=BASE_URL?>/js/compiled-extended-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>

    <!-- app -->
    <script src="<?=BASE_URL?>/js/compiled-app.min.js?v=<?php echo $settings->appVersion; ?>"></script>
</head>

<script type="text/javascript">
    jQuery(document).ready(function(){
        
        if(jQuery('.login-alert .alert').text() != ''){
            jQuery('.login-alert').fadeIn();
        }

    });
</script>

<?php

    $redirectUrl = BASE_URL."/dashboard/show";

    if($_SERVER['REQUEST_URI'] != '' && isset($_GET['logout']) === false) {
        $redirectUrl = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    }

?>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm">

    <div class="logo" style="margin-left:0px;">
        <a href="<?=BASE_URL ?>/" style="background-image:url(<?php echo htmlentities($_SESSION["companysettings.logoPath"]);?>)">&nbsp;</a>
    </div>

</div>

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">
        <div class="row">
            <div class="col-md-5">

            </div>
            <div class="col-md-6" style="position:relative;">
                <h1 style="font-family:Exo;  font-size: 64px; padding-left:15px; font-weight:400;"><?php echo $language->__("headlines.drive_impact"); ?></h1>

                <span class="iq-objects-04 iq-fadebounce">
				    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight"  style="">

        <div class="regpanel">
            <div class="regpanelinner">
                <a href="<?=BASE_URL ?>" target="_blank"><img src="<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>" /></a>

                <div class="pageheader">

                    <div class="pageicon"><span class="iconfa-signin"></span></div>
                    <div class="pagetitle">
                        <h5><?php echo htmlentities($_SESSION["companysettings.sitename"]); ?></h5>
                        <h1><?php echo $this->language->__("headlines.login"); ?></h1>
                    </div>
                </div>
                <div class="regcontent"  style="margin-left: 90px;">
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
                        </div>
                        <div class="">
                            <a href="<?=BASE_URL ?>/resetPassword" style="float:right; margin-top:10px;"><?php echo $this->language->__("links.forgot_password"); ?></a>
                            <input type="submit" name="login" value="<?php echo $this->language->__("buttons.login"); ?>" class="btn btn-primary"/>
                        </div>

                    </form>
                </div>
            </div>
        </div>



    </div>
</div>

</body>
</html>
