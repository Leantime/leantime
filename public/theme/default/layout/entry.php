<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontcontroller->includeAction('pageparts.header'); ?>

</head>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm">

    <div class="logo" style="margin-left:0px;">
        <a href="<?=BASE_URL ?>/" style="background-image:url(<?php echo htmlentities($_SESSION["companysettings.logoPath"]);?>)">&nbsp;</a>
    </div>

</div>


<style>
 .leantimeLogo { position: fixed; bottom: 10px; right: 10px; }
</style>

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

                <!--###MAINCONTENT###-->

            </div>
        </div>

    </div>
    <div class="leantimeLogo">
        <img style="height: 25px;" src="<?=BASE_URL ?>/dist/images/logo-powered-by-leantime.png">
    </div>
</div>

<?php echo $this->frontcontroller->includeAction('pageparts.pageBottom'); ?>
