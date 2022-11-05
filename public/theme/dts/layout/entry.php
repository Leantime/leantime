<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontcontroller->includeAction('general.header'); ?>

</head>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm">

    <div class="logo" style="margin-left:0px;">
        <a href="<?=BASE_URL ?>/" style="background-image:url(<?php echo htmlentities($_SESSION["companysettings.logoPath"]);?>)">&nbsp;</a>
    </div>

</div>

<style>
 .iq-objects-04 { right: 0%; }
 .leantimeLogo { position: fixed; bottom: 10px; right: 10px; }
</style>

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">
        <div class="row">
            <div class="col-md-12" style="position:relative;">
                <h1 class="mainWelcome"><img  style="width: 600px" src="<?=BASE_URL ?>/theme/dts/images/logo-dts-welcome-white.png"></h1>
                <span class="iq-objects-04 iq-fadebounce">
				    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight">

        <div class="regpanel">
            <div class="regpanelinner">
                <a href="<?=BASE_URL ?>" target="_blank"><img src="<?=BASE_URL ?>/theme/dts/images/logo-id.png" /></a>

                <!--###MAINCONTENT###-->

            </div>
        </div>
    </div>
	<div class="leantimeLogo">
		<img style="height: 25px;" src="<?=BASE_URL ?>/images/logo-powered-by-leantime.png">
	</div>
</div>

<?php echo $this->frontcontroller->includeAction('general.pageBottom'); ?>
