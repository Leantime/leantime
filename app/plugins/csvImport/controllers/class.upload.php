<?php

namespace leantime\plugins\controllers {

    use League\Csv\Statement;
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
        private csvImport $providerService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init(csvImport $providerService)
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $this->providerService = $providerService;
        }

        public function get()
        {
            $this->tpl->displayPartial("csvImport.upload");
        }

        public function post($params)
        {
            $csv = Reader::createFromPath($_FILES['file']['tmp_name'], 'r');

            $csv->setHeaderOffset(0);

            $records = Statement::create()->process($csv);

            $header = $records->getHeader();  //returns the CSV header record
            $records = $csv->getRecords(); //returns all the CSV records as an Iterator object

            $rows = array();
            foreach ($records as $offset => $record) {
                $rows[] = $record;
            }

            $integration = app()->make(models\connector\integration::class);
            $integration->fields = implode(",", $header);

            //Temporarily store results in meta
            $integration->meta = serialize($rows);

            $integrationService = app()->make(services\connector\integrations::class);
            $id = $integrationService->create($integration);

            $this->tpl->displayJson(json_encode(array("id" => $id)));
        }
    }
}
