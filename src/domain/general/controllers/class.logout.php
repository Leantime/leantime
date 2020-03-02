<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class logout
    {

        public function run()
        {

            $login = new core\login(core\session::getSID());

            if ($login->logged_in() === true) {

                header("Location:".BASE_URL."/index.php?logout=1");
            }
        }
    }
}
