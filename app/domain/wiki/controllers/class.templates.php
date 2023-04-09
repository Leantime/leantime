<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\models\wiki;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class templates extends controller
    {

        private services\wiki $wikiService;
        private services\comments $commentService;

        public function init()
        {
        }

        public function get($params)
        {
            $this->tpl->displayPartial("wiki.templates");
        }


    }
}
