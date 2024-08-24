<?php

namespace Leantime\Domain\Connector\Controllers {

    use http\Client\Response;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
    use Leantime\Domain\Connector\Repositories\LeantimeEntities;
    use Leantime\Domain\Connector\Services\Connector;
    use Leantime\Domain\Connector\Services\Integrations as IntegrationService;
    use Leantime\Domain\Connector\Services\Providers;

    /**
     *
     */
    class Integration extends Controller
    {
        private Providers $providerService;
        private IntegrationService $integrationService;
        private LeantimeEntities $leantimeEntities;
        private array $values = array();
        private array $fields = array();

        private Connector $connectorService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            Providers $providerService,
            IntegrationService $integrationService,
            LeantimeEntities $leantimeEntities,
            Connector $connectorService
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            $this->providerService = $providerService;
            $this->leantimeEntities = $leantimeEntities;
            $this->integrationService = $integrationService;
            $this->connectorService = $connectorService;
        }


        /**
         * run - handle post
         *
         * @access public
         *
         */
        public function run()
        {

            $params = $_REQUEST;
            if (!session()->exists("currentImportEntity")) {
                session(["currentImportEntity" => '']);
            }

            if (isset($params["provider"])) {
                //New integration with provider
                //Get the provider
                $provider = $this->providerService->getProvider($params["provider"]);
                $this->tpl->assign("provider", $provider);

                $currentIntegration = app()->make(IntegrationModel::class);

                if (isset($params["integrationId"])) {
                    $currentIntegration = $this->integrationService->get($params["integrationId"]);
                    $this->tpl->assign("integrationId", $currentIntegration->id);
                }


                /* Steps + + + + + + + + + + + + + + + + + + + + + + + */

                //STEP 0: No Step defined, new integration
                if (!isset($params["step"])) {
                    return $this->tpl->display('connector.newIntegration');
                }

                //STEP 1: Initiate connection
                if ($params["step"] == "connect") {
                    //This should handle connection UI
                    $connection = $provider->connect();

                    if($connection instanceof \Symfony\Component\HttpFoundation\Response) {
                        return $connection;
                    }
                }


                //STEP 2: Choose Entities to sync
                if ($params["step"] == "entity") {
                    $this->tpl->assign("providerEntities", $provider->getEntities());
                    $this->tpl->assign("leantimeEntities", $this->leantimeEntities->availableLeantimeEntities);

                    //TODO UI to show entity picker/mapper
                    return $this->tpl->display('connector.integrationEntity');
                }

                //STEP 3: Choose Entities to sync
                if ($params["step"] == "fields") {
                    if (isset($_POST['leantimeEntities'])) {
                        $entity = $_POST['leantimeEntities'];
                        session(["currentImportEntity" => $entity]);
                    } else if (session()->exists("currentImportEntity") && session("currentImportEntity") != "") {
                        $entity = session("currentImportEntity");
                    } else {
                        $this->tpl->setNotification("Entity not set", "error");

                        return Frontcontroller::redirect(BASE_URL . "/connector/integration?provider=" . $provider->id . "");
                    }

                    $currentIntegration->entity = $entity;

                    $flags = $this->connectorService->getEntityFlags($entity);

                    $this->integrationService->patch($currentIntegration->id, array("entity" => $entity));

                    if (isset($currentIntegration->fields) && $currentIntegration->fields != '') {
                        $this->tpl->assign("providerFields", explode(",", $currentIntegration->fields));
                    } else {
                        $this->tpl->assign("providerFields", $provider->getFields());
                    }
                    $this->tpl->assign("flags", $flags);
                    $this->tpl->assign("leantimeFields", $this->leantimeEntities->availableLeantimeEntities[$entity]['fields']);
                    return $this->tpl->display('connector.integrationFields');
                }

                //STEP 4: Choose Entities to sync
                if ($params["step"] == "sync") {
                    //TODO UI to show sync schedule/options
                    return $this->tpl->display('connector.integrationSync');
                }

                //STEP 5: import Review
                if ($params["step"] == "parse") {
                    $this->values = $provider->geValues();

                    //Fetching the field matching and putting it in an array
                    $this->fields = array();
                    $this->fields = $this->connectorService->getFieldMappings($_POST);

                    $flags = array();
                    $flags = $this->connectorService->parseValues($this->fields, $this->values, session("currentImportEntity"));

                    //show the imported data as confirmation
                    $this->tpl->assign("values", $this->values);
                    $this->tpl->assign("fields", $this->fields);
                    $this->tpl->assign("flags", $flags);

                    return $this->tpl->display('connector.integrationImport');
                }

                //STEP 6: Do the import
                if ($params["step"] == "import") {
                    //Store data in DB
                    $values = unserialize(session("serValues"));
                    $fields = unserialize(session("serFields"));

                    //confirm and store in DB
                    $this->connectorService->importValues($fields, $values, session("currentImportEntity"));

                    //display stored successfully message
                    return $this->tpl->display('connector.integrationConfirm');
                }
            }
        }
    }
}
