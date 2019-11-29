<!DOCTYPE html>
<html>
<head>
    <title><?php echo $_SESSION["companysettings.sitename"] ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="theme-color" content="#<?php echo $_SESSION["companysettings.mainColor"] ?>" />

    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <?php echo $frontController->includeAction('general.header'); ?>

    <link rel="stylesheet" href="/css/main.css?v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="/css/style.default.css?v=<?php echo $settings->appVersion; ?>" type="text/css"/>
    <link rel="stylesheet" href="/css/style.custom.php?color=<?php echo $_SESSION["companysettings.mainColor"] ?>&v=<?php echo $settings->appVersion; ?>" type="text/css"/>

    <script src="/api/i18n"></script>

    <!-- libs -->
    <script src="/js/compiled-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>

    <!-- app -->
    <script src="/js/compiled-app.min.js?v=<?php echo $settings->appVersion; ?>"></script>

    <!--###HEAD##-->

</head>

<body>
<div class="mainwrapper">

    <div class="header">

        <div class="logo" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">
            <a class="barmenu <?php if(!isset($_SESSION['menuState']) || $_SESSION['menuState'] == 'open') echo 'open'; ?>" href="javascript:void(0);"></a>
            <a href="/" style="background-image:url('<?php echo  $_SESSION["companysettings.logoPath"]; ?>')">&nbsp;</a>
        </div>
        <div class="headerinner" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">
            <div class="userloggedinfo">
                <?php echo $frontController->includeAction('general.loginInfo'); ?>    
            </div>

            <?php echo $frontController->includeAction('general.headMenu'); ?>

        </div>
    </div>

    <div class="leftpanel" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">

        <div class="leftmenu">

            <?php echo $frontController->includeAction('general.menu'); ?>

        </div><!--leftmenu-->

    </div><!-- leftpanel -->


    <div class="rightpanel" style="position: relative; <?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">

        <!--###MAINCONTENT###-->


        <div class='footer'>
            <?php echo $frontController->includeAction('general.footer'); ?>
        </div>

    </div><!--rightpanel-->

</div><!--mainwrapper-->

<!--###FOOTER###-->

</body>
</html>
