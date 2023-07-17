<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php $this->dispatchTplEvent('beforeFooterOpen'); ?>
<div class="footer" style="padding-right:50px;">
    <?php $this->dispatchTplEvent('afterFooterOpen'); ?>
    <a href="http://leantime.io" target="_blank"><img style="height: 18px; opacity:0.5; vertical-align:sub;" src="<?=BASE_URL?>/dist/images/logo-powered-by-leantime.png"></a>
    <span style="color:var(--primary-font-color); opacity:0.5;">v<?=$this->get("version");?></span>
    <?php $this->dispatchTplEvent('beforeFooterClose'); ?>
</div>
<?php $this->dispatchTplEvent('afterFooter'); ?>

