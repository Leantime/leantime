<?php

namespace leantime\plugins\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;
    use leantime\domain\services\auth;
    use League\Csv\Reader;
    use leantime\plugins\services\csvImport;

    class upload extends controller
    {


        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $this->providerService = new csvImport();

        }


        public function get()
        {


           $this->tpl->displayPartial("csvImport.upload");
        }

        public function post($params)
        {

            $csv = Reader::createFromPath($_FILES['file']['tmp_name'], 'r');

            $csv->setHeaderOffset(0);

            $header = $csv->getHeader(); //returns the CSV header record
            $records = $csv->getRecords(); //returns all the CSV records as an Iterator object


            $this->providerService->setFields($header);

            $_SESSION['csvImporter']['records'] =   $records;

            $this->tpl->displayPartial("csvImport.upload");
        }

    }
}
