<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<?php $tpl->dispatchTplEvent('beforeFooterOpen'); ?>
<div class="footer" style="padding-right:50px;">
    <?php $tpl->dispatchTplEvent('afterFooterOpen'); ?>
    <a href="http://leantime.io" target="_blank"><img style="height: 18px; opacity:0.5; vertical-align:sub;" src="<?=BASE_URL?>/dist/images/logo-powered-by-leantime.png"></a>
    <span style="color:var(--primary-font-color); opacity:0.5;">v<?=$tpl->get("version");?></span>
    <?php $tpl->dispatchTplEvent('beforeFooterClose'); ?>
</div>
<?php $tpl->dispatchTplEvent('afterFooter'); ?>

