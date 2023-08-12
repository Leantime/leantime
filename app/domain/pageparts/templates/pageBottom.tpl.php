<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
$appSettings = $tpl->get('appSettings');
?>

<?php if (isset($_SESSION['do_cron'])) { ?>
    <script>
        var req = new XMLHttpRequest();
        req.open("GET", "<?=BASE_URL?>/cron/run",true);
        req.send(null);
    </script>
<?php } ?>

<script>
    //5 min time to run cron
    setInterval(function(){
        jQuery.get('<?=BASE_URL?>/cron/run');
    }, 300000);
</script>

<?php if (isset($_SESSION['userdata'])) { ?>
    <script>
        //5 min keep alive timer
        setInterval(function(){
            jQuery.get(leantime.appUrl+'/auth/keepAlive');
            }, 300000);
    </script>

<?php } ?>

<?php $tpl->dispatchTplEvent('beforeBodyClose'); ?>

<script src="<?=BASE_URL?>/dist/js/compiled-footer.<?php echo $appSettings->appVersion; ?>.min.js"> </script>
</body>
</html>
