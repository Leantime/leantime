<?php defined('RESTRICTED') or die('Restricted access'); ?>

<p><a href="http://leantime.io" target="_blank">Powered By Leantime</a> - <?=$this->get("version");?></p>
<script>
    var req = new XMLHttpRequest();
    req.open("GET", "<?=BASE_URL?>c/cron.php",true);
    req.send(null);
</script>
