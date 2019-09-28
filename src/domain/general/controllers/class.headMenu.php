<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class headMenu
    {

        public function run()
        {

            $login = new core\login(core\session::getSID());

            if ($login->logged_in() === true) {

                $tpl = new core\template();

                //Tickets
                $tickets = new repositories\tickets();

                $tpl->assign("onTheClock", $tickets->isClocked($_SESSION["userdata"]["id"]));

                $tpl->displayPartial("general.headMenu");


            }
        }
    }

}
