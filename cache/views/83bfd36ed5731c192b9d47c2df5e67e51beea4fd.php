<?php if($poorMansCron): ?>
    <script>
        jQuery.get('<?php echo BASE_URL; ?>/cron/run');
        //1 min time to run cron
        setInterval(function(){
            jQuery.get('<?php echo BASE_URL; ?>/cron/run');
        }, 60000);
    </script>
<?php endif; ?>

<?php if($loggedIn): ?>
    <script>
        //5 min keep alive timer
        setInterval(function(){
            jQuery.get(leantime.appUrl+'/auth/keepAlive');
        }, 300000);
    </script>
<?php endif; ?>

<script src="<?php echo BASE_URL; ?>/dist/js/compiled-footer.<?php echo $version; ?>.min.js"></script>

<?php $tpl->dispatchTplEvent('beforeBodyClose'); ?>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/sections/pageBottom.blade.php ENDPATH**/ ?>