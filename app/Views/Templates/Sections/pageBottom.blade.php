@if ($runCron)
<script>
    var req = new XMLHttpRequest();
    req.open("GET", "{!! BASE_URL !!}/cron/run",true);
    req.send(null);
</script>
@endif

<script>
    //5 min time to run cron
    setInterval(function(){
        jQuery.get('<?=BASE_URL?>/cron/run');
    }, 300000);
</script>

@if ($loggedIn)
<script>
    //5 min keep alive timer
    setInterval(function(){
        jQuery.get(leantime.appUrl+'/auth/keepAlive');
    }, 300000);
</script>
@endif

<script src="{!! BASE_URL !!}/dist/js/compiled-footer.{!! $version !!}.min.js"></script>

@dispatchEvent('beforeBodyClose')
