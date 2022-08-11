<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class logout
    {

        public function run()
        {
            if (services\auth::logged_in() === true) {

                header("Location:".BASE_URL."/index.php?logout=1");
            }
        }
    }
}
