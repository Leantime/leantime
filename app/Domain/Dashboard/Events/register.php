<?php

namespace Leantime\Domain\Dashboard\Events {

    use Leantime\Core\Events;

    /**
     *
     */
    class DashboardTest
    {
        /**
         * @param $payload
         * @return void
         */
        /**
         * @param $payload
         * @return void
         */
        public function handle($payload): void
        {
            // code here
        }
    }

    Events::add_event_listener("core.application.start.beginning", app()->make(dashboardTest::class));

}
