<?php

namespace leantime\domain\events {

    class DashboardTest
    {
        public function handle($payload)
        {
            // code here
        }
    }

    \leantime\core\events::add_event_listener("core.application.start.beginning", new dashboardTest());

}
