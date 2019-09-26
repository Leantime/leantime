<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $_SESSION["companysettings.sitename"] ?></title>

    <meta name="description" content="TimelineJS example">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <?php echo $frontController->includeAction('general.header'); ?>

    <link rel="stylesheet" href="/css/bootstrap-timepicker.min.css" type="text/css"/>
    <link rel="stylesheet" href="/css/bootstrap-fileupload.min.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="/js/libs/simple-color-picker-master/jquery.simple-color-picker.css"/>
    <link rel="stylesheet" type="text/css" href="/js/libs/simpleGantt/frappe_theme.css"/>
    <link rel="stylesheet" type="text/css" href="/js/libs/jquery.nyroModal/styles/nyroModal.css"/>
    <link rel="stylesheet" type="text/css" href="/css/shepherd-theme-arrows.css"/>

    <link rel="stylesheet" href="/css/style.default.css?v=<?php echo $settings->appVersion; ?>" type="text/css"/>
    <link rel="stylesheet" href="/css/style.custom.php?color=<?php echo $_SESSION["companysettings.mainColor"] ?>&v=<?php echo $settings->appVersion; ?>" type="text/css"/>
    <link rel="stylesheet" href="/css/main.css"/>

    <!-- libs -->


    <script type="text/javascript" src="/js/libs/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="/js/libs/jquery-migrate-1.1.1.min.js"></script>
    <script type="text/javascript" src="/js/libs/jquery-ui-1.9.2.min.js"></script>
    <script type="text/javascript" src="/js/libs/modernizr.min.js"></script>
    <script type="text/javascript" src="/js/libs/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.cookie.js"></script>
    <script type="text/javascript" src="\js\libs\tinymce_4.7.9\tinymce\js\tinymce\jquery.tinymce.min.js"></script>
    <script type="text/javascript" src="\js\libs\tinymce_4.7.9\tinymce\js\tinymce\tinymce.min.js"></script>
    <script type="text/javascript" src="/js/libs/chosen.jquery.min.js"></script>

    <script type="text/javascript" src="/js/libs/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="/js/libs/bootstrap-fileupload.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.pie.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.symbol.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.fillbetween.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.crosshair.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.stack.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.resize.min.js"></script>
    <script type="text/javascript" src="/js/libs/flot/jquery.flot.time.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.isotope.min.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.colorbox-min.js"></script>

    <script type="text/javascript" src="/js/libs/jquery.jgrowl.js"></script>

    <script type="text/javascript" src="/js/libs/jquery.form.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.tagsinput.min.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.datatable-columnFilter.js"></script>
    <script type="text/javascript" src="/js/libs/jquery.dataTables.sorting.js"></script>
    <script type="text/javascript" src="/js/libs/ListJS/list.min.js"></script>

    <script type="text/javascript" src="/js/libs/fullcalendar.min.js"></script>

    <script type="text/javascript" src="/js/libs/simple-color-picker-master/jquery.simple-color-picker.js"></script>
    <script type="text/javascript" src="/js/libs/colorpicker.js"></script>


    <script type="text/javascript" src="/js/libs/simpleGantt/moment.min.js"></script>
    <script type="text/javascript" src="/js/libs/simpleGantt/snap.svg-min.js"></script>
    <script type="text/javascript" src="/js/libs/simpleGantt/frappe-gantt.min.js?v=2.1"></script>

    <!-- Quickselect  -->
    <script src="/js/libs/Quickselect/quicksilver.js" type="text/javascript"></script>
    <script src="/js/libs/Quickselect/jquery.quickselect.js" type="text/javascript"></script>

    <link rel="stylesheet" type="text/css" href="/js/libs/Quickselect/jquery.quickselect.css"/>

    <!-- Tablsorter Simple  -->
    <script src="/js/libs/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script src="/js/libs/jquery.tablesorter.pager.js" type="text/javascript"></script>

    <script type="text/javascript" src="/js/libs/touchpunch.js"></script>

    <!--[if lte IE 8]>
    <script language="javascript" type="text/javascript" src="/js/libs/excanvas.min.js"></script>
    <![endif]-->

    <script type="text/javascript" src="/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js"></script>

    <!-- app -->
    <script src="/js/compiled-libs.min.js?v=2.0.4"></script>
    <script src="/js/compiled-app.min.js?v=2.0.4"></script>

    <!--###HEAD##-->

</head>

<body>
<div class="mainwrapper">

    <div class="header">

        <div class="logo" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">
            <a class="barmenu <?php if(!isset($_SESSION['menuState']) || $_SESSION['menuState'] == 'open') echo 'open'; ?>" href="javascript:void(0);"></a>
            <a href="/" style="background-image:url(<?php echo  $_SESSION["companysettings.logoPath"]; ?>">&nbsp;</a>
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
