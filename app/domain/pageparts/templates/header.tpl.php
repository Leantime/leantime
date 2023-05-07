<?php
    defined('RESTRICTED') or die('Restricted access');

    $appSettings = $this->get('appSettings');
    $debugRenderer = $this->get('debugRenderer');
    $themeCore = new \leantime\core\theme();
    $theme = $this->get('theme');
?>

<title><?php $this->e($this->dispatchTplFilter('page_title', $_SESSION["companysettings.sitename"])); ?></title>

<meta name="description" content="<?php $this->e($_SESSION["companysettings.sitename"]) ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="theme-color" content="<?php $this->e($_SESSION["companysettings.primarycolor"]) ?>">
<meta name="color-scheme" content="<?php $this->e($theme ?? 'default') ?>">
<meta name="identifier-URL" content="<?=BASE_URL?>">
<?php $this->dispatchTplEvent('afterMetaTags'); ?>

<link rel="shortcut icon" href="<?=BASE_URL?>/images/favicon.png"/>
<link rel="apple-touch-icon" href="<?=BASE_URL?>/images/apple-touch-icon.png">

<link rel="stylesheet" href="<?=BASE_URL?>/css/main.<?php echo $settings->appVersion; ?>.css"/>
<?php $this->dispatchTplEvent('afterLinkTags'); ?>

<script src="<?=BASE_URL?>/api/i18n"></script>

<!-- libs -->
<script src="<?=BASE_URL?>/js/compiled-base-libs.<?php echo $settings->appVersion; ?>.min.js"></script>
<script src="<?=BASE_URL?>/js/compiled-extended-libs.<?php echo $settings->appVersion; ?>.min.js"></script>
<?php $this->dispatchTplEvent('afterScriptLibTags'); ?>

<!-- app -->
<script src="<?=BASE_URL?>/js/compiled-app.<?php echo $settings->appVersion; ?>.min.js"></script>
<?php $this->dispatchTplEvent('afterMainScriptTag'); ?>

<!-- theme -->
<?php $jsUrl = $themeCore->getJsUrl(); if ($jsUrl !== false) { ?>
    <script src="<?=$jsUrl ?>"></script>
<?php } ?>

<?php $styleUrl = $themeCore->getStyleUrl(); if ($styleUrl !== false) { ?>
    <link rel="stylesheet" id="themeStylesheet" href="<?=$themeCore->getStyleUrl(); ?>"/>
<?php } ?>

<?php $this->dispatchTplEvent('afterThemeScripts'); ?>

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


<?php $this->dispatchTplEvent('afterThemeColors'); ?>

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
<?php $this->dispatchTplEvent('afterCustomizeTags'); ?>
