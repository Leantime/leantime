<?php

namespace leantime\domain\repositories\connector {

    use leantime\core\repository;
    use leantime\core\service;
    use leantime\domain\models\connector\integration;

    class integrations extends repository
    {
        public function __construct()
        {
            $this->entity = "integrations";
            $this->model = integration::class;
        }
    }
}
