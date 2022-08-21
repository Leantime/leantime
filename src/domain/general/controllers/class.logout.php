<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services\auth;

    class logout
    {

        public function run()
        {
            if (auth::logged_in() === true) {

                header("Location:".BASE_URL."/index.php?logout=1");
            }
        }
    }
}
