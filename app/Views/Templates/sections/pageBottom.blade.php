@if ($poorMansCron && $loggedIn)
    <script>

        let now = Date.now();
        let lastCronExecution = localStorage.getItem("lastCronRun");

        if(Number.isInteger(lastCronExecution)){

            var difference = Math.floor((now - lastCronExecution) / 1000);
            if(difference > 300) {
                jQuery.get('{!! BASE_URL !!}/cron/run');
                localStorage.setItem("lastCronRun", Date.now());
            }

        }else{
            jQuery.get('{!! BASE_URL !!}/cron/run');
            localStorage.setItem("lastCronRun", Date.now());
        }

        //1 min time to run cron
        setInterval(function(){
            jQuery.get('{!! BASE_URL !!}/cron/run');
            localStorage.setItem("lastCronRun", Date.now());
        }, 300000);

    </script>
@endif

<script src="@mix('js/compiled-footer.js')"></script>

@dispatchEvent('beforeBodyClose')
