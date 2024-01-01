<?php

use Leantime\Core\Theme;

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}

    $appSettings = $tpl->get('appSettings');
    $debugRenderer = $tpl->get('debugRenderer');
    $themeCore = app()->make(Theme::class);
    $theme = $tpl->get('theme');
?>

<title><?php $tpl->e($tpl->dispatchTplFilter('page_title', $_SESSION["companysettings.sitename"])); ?></title>

<meta name="description" content="<?php $tpl->e($_SESSION["companysettings.sitename"]) ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="theme-color" content="<?php $tpl->e($_SESSION["companysettings.primarycolor"]) ?>">
<meta name="color-scheme" content="<?php $tpl->e($theme ?? 'default') ?>">
<meta name="identifier-URL" content="<?=BASE_URL?>">
<meta name="leantime-version" content="<?=$settings->appVersion ?>">
<meta name="view-transition" content="same-origin" />
<?php $tpl->dispatchTplEvent('afterMetaTags'); ?>

<link rel="shortcut icon" href="<?=BASE_URL?>/dist/images/favicon.png"/>
<link rel="apple-touch-icon" href="<?=BASE_URL?>/dist/images/apple-touch-icon.png">

<link rel="stylesheet" href="<?=BASE_URL?>/dist/css/main.<?php echo $settings->appVersion; ?>.min.css"/>
<?php $tpl->dispatchTplEvent('afterLinkTags'); ?>

<script src="<?=BASE_URL?>/api/i18n"></script>

<!-- libs -->
<script src="<?=BASE_URL?>/dist/js/compiled-frameworks.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-global-component.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-calendar-component.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-table-component.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-editor-component.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-gantt-component.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-chart-component.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/dist/js/compiled-app.<?php echo $settings->appVersion; ?>.min.js"></script>


<?php $tpl->dispatchTplEvent('afterScriptLibTags'); ?>

<!-- app -->
<script src="<?=BASE_URL?>/dist/js/compiled-app.<?php echo $settings->appVersion; ?>.min.js"></script>
<?php $tpl->dispatchTplEvent('afterMainScriptTag'); ?>

<!-- theme -->
<?php $jsUrl = $themeCore->getJsUrl(); if ($jsUrl !== false) { ?>
    <script src="<?=$jsUrl ?>"></script>
<?php } ?>

<?php $styleUrl = $themeCore->getStyleUrl(); if ($styleUrl !== false) { ?>
    <link rel="stylesheet" id="themeStylesheet" href="<?=$themeCore->getStyleUrl(); ?>"/>
<?php } ?>

<?php $tpl->dispatchTplEvent('afterThemeScripts'); ?>

<!-- Replace main theme colors -->
<style>
    :root{
        <?php if (isset($_SESSION["companysettings.primarycolor"])) { ?>
            --accent1: <?=htmlentities($_SESSION["companysettings.primarycolor"]);?>;
        <?php } ?>
        <?php if (isset($_SESSION["companysettings.secondarycolor"])) { ?>
            --accent2: <?=htmlentities($_SESSION["companysettings.secondarycolor"]);?>;
        <?php } ?>
    }
</style>


<?php $tpl->dispatchTplEvent('afterThemeColors'); ?>

<!-- customize -->
<?php $jsUrl = $themeCore->getJsUrl(); if ($jsUrl !== false) { ?>
    <script src="<?=$jsUrl ?>"></script>
<?php } ?>
<?php $customJsUrl = $themeCore->getCustomJsUrl(); if ($customJsUrl !== false) { ?>
    <script src="<?=$customJsUrl ?>"></script>
<?php } ?>
<?php $customStyleUrl = $themeCore->getCustomStyleUrl(); if ($styleUrl !== false) { ?>
    <link rel="stylesheet" href="<?=$customStyleUrl ?>" />
<?php } ?>
<?php $tpl->dispatchTplEvent('afterCustomizeTags'); ?>
