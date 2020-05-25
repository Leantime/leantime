<?php defined('RESTRICTED') or die('Restricted access'); ?>

<title><?php $this->e($_SESSION["companysettings.sitename"]) ?></title>

<meta name="description" content="<?php $this->e($_SESSION["companysettings.sitename"]) ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="theme-color" content="#<?php $this->e($_SESSION["companysettings.mainColor"]) ?>">
<meta name="identifier-URL" content="<?=BASE_URL?>">

<link rel="shortcut icon" href="<?=BASE_URL?>/favicon.ico"/>
<link rel="apple-touch-icon" href="<?=BASE_URL?>/apple-touch-icon.png">