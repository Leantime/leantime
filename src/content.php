<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->includeAction('general.header'); ?>

    <link rel="stylesheet" href="<?=BASE_URL?>/css/main.css?v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/style.default.css?v=<?php echo $settings->appVersion; ?>" type="text/css"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/style.custom.php?color=<?php echo htmlentities($_SESSION["companysettings.mainColor"]) ?>&v=<?php echo $settings->appVersion; ?>" type="text/css"/>

    <script src="<?=BASE_URL?>/api/i18n"></script>

    <!-- libs -->
    <script src="<?=BASE_URL?>/js/compiled-base-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>
    <script src="<?=BASE_URL?>/js/compiled-extended-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>

    <!-- app -->
    <script src="<?=BASE_URL?>/js/compiled-app.min.js?v=<?php echo $settings->appVersion; ?>"></script>

</head>

<body>
<div class="mainwrapper">

    <div class="header">

        <div class="logo" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">
            <a class="barmenu <?php if(!isset($_SESSION['menuState']) || $_SESSION['menuState'] == 'open') echo 'open'; ?>" href="javascript:void(0);"></a>
            <a href="<?=BASE_URL ?>" style="background-image:url('<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>')">&nbsp;</a>
        </div>
        <div class="headerinner" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">
            <div class="userloggedinfo">
                <?php echo $this->includeAction('general.loginInfo'); ?>
            </div>

            <?php echo $this->includeAction('general.headMenu'); ?>

        </div>
    </div>

    <div class="leftpanel" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">

        <div class="leftmenu">

            <?php echo $this->includeAction('general.menu'); ?>

        </div><!--leftmenu-->

    </div><!-- leftpanel -->


    <div class="rightpanel" style="position: relative; <?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">

        <!--###MAINCONTENT###-->

        <div class='footer'>
            <?php echo $this->includeAction('general.footer'); ?>
        </div>

    </div><!--rightpanel-->

</div><!--mainwrapper-->

</body>
</html>
