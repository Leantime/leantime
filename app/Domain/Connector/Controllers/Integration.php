<?php

namespace Leantime\Domain\Connector\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Connector\Services\Providers;
    use Leantime\Domain\Connector\Services\Integrations as IntegrationService;
    use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
    use Leantime\Domain\Connector\Repositories\LeantimeEntities;
    use Leantime\Domain\Auth\Services\Auth;

    class Integration extends Controller
    {
        private Providers $providerService;
        private IntegrationService $integrationService;
        private LeantimeEntities $leantimeEntities;

        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            Providers $providerService,
            IntegrationService $integrationService,
            LeantimeEntities $leantimeEntities
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->providerService = $providerService;
            $this->leantimeEntities = $leantimeEntities;
            $this->integrationService = $integrationService;
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

                //Initiate connection
                if (isset($params["step"])  && $params["step"] == "connect") {
                    //This should handle connection UI
                    $provider->connect();
                }

                //Choose Entities to sync
                if (isset($params["step"])  && $params["step"] == "entity") {
                    $this->tpl->assign("providerEntities", $provider->getEntities());
                    $this->tpl->assign("leantimeEntities", $this->leantimeEntities->availableLeantimeEntities);

                    //TODO UI to show entity picker/mapper
                    $this->tpl->display('connector.integrationEntity');
                }

                //Choose fields to map
                //Choose Entities to sync
                if (isset($params["step"])  && $params["step"] == "fields") {
                    $entity = $_POST['leantimeEntities'];
                    $currentIntegration->entity = $entity;

                    $this->integrationService->patch($currentIntegration->id, array("entity" => $entity));

                    //TODO UI to show field picker/mapper
                    if (isset($currentIntegration->fields) && $currentIntegration->fields != '') {
                        $this->tpl->assign("providerFields", explode(",", $currentIntegration->fields));
                    } else {
                        $this->tpl->assign("providerFields", $provider->getFields());
                    }
                    $this->tpl->assign("leantimeFields", $this->leantimeEntities->availableLeantimeEntities[$entity]['fields']);
                    $this->tpl->display('connector.integrationFields');
                }

                if (isset($params["step"])  && $params["step"] == "sync") {
                    //TODO UI to show sync schedule/options

                    $this->tpl->display('connector.integrationSync');
                }

                if (isset($params["step"])  && $params["step"] == "import") {
                    //TODO UI to show sync schedule/options

                    $this->tpl->display('connector.integrationSync');
                }

                if (isset($params["step"])  && $params["step"] == "confirm") {
                    //confirm and store in DB
                    $this->tpl->display('connector.integrationConfirm');
                }

                if (!isset($params["step"])) {
                    $this->tpl->display('connector.newIntegration');
                }
            }
        }
    }

}
