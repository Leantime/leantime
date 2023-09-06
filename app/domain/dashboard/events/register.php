<?php

namespace Leantime\Domain\Dashboard\Events {

    class DashboardTest
    {
        public function handle($payload)
        {
            // code here
        }
    }

    \Leantime\Core\Events::add_event_listener("core.application.start.beginning", app()->make(dashboardTest::class));

}
