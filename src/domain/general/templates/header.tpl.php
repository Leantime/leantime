<?php
defined('RESTRICTED') or die('Restricted access');

$appSettings = $this->get('appSettings');
$debugRenderer = $this->get('debugRenderer');
$themeCore = new \leantime\core\theme();
$theme = $this->get('theme');
?>

<title><?php $this->e($_SESSION["companysettings.sitename"]) ?></title>

<meta name="description" content="<?php $this->e($_SESSION["companysettings.sitename"]) ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="theme-color" content="<?php $this->e($_SESSION["companysettings.primarycolor"]) ?>">
<meta name="color-scheme" content="<?php $this->e($theme ?? 'default') ?>">
<meta name="identifier-URL" content="<?=BASE_URL?>">

<link rel="shortcut icon" href="<?=BASE_URL?>/images/favicon.png"/>
<link rel="apple-touch-icon" href="<?=BASE_URL?>/images/apple-touch-icon.png">

<?php $styleUrl = $themeCore->getStyleUrl(); if($styleUrl !== false) { ?>     
    <link rel="stylesheet" href="<?=$themeCore->getStyleUrl(); ?>"/>
<?php } ?>                                                           
<link rel="stylesheet" href="<?=BASE_URL?>/css/main.css?v=<?php echo $settings->appVersion; ?>"/>

<script src="<?=BASE_URL?>/api/i18n"></script>


<!-- libs -->
<script src="<?=BASE_URL?>/js/compiled-base-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>
<script src="<?=BASE_URL?>/js/compiled-extended-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>

<!-- app -->
<script src="<?=BASE_URL?>/js/compiled-app.min.js?v=<?php echo $settings->appVersion; ?>"></script>

<!-- Replace main theme colors -->
<style>
    :root{
        --accent1: <?=htmlentities($_SESSION["companysettings.primarycolor"]);?>;
        --accent2: <?=htmlentities($_SESSION["companysettings.secondarycolor"]);?>;
    }
</style>

<!-- customize -->
<?php $customStyleUrl = $themeCore->getCustomStyleUrl(); if($styleUrl !== false) { ?>     
    <link rel="stylesheet" href="<?=$customStyleUrl ?>" />
<?php } ?>                                                           
     
