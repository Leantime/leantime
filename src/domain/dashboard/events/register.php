<?php

class dashboardTest {

    public function handle($eventname, $payload) {

    }

}

\leantime\core\events::add_event_listener("application.start.beginning", new dashboardTest);
