<?php

namespace leantime\domain\services;

    use Exception;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\eventhelpers;
    use RobThree\Auth\TwoFactorAuth;

    class Api
    {
        use eventhelpers;


        /**
         * __construct
         *
         */
        public function __construct()
        {
            $this->apiRepository = new repositories\Api();

        }

        public function getAPIKeyUser($apiKey): bool|array
        {

            //Split apiKey into parts
            $apiKeyParts = explode(".", $apiKey);

            if(!is_array($apiKeyParts) || count($apiKeyParts) != 3) {
                error_log("Not a valid API Key format");
                return false;
            }

            $namespace = $apiKeyParts[0];
            $user = $apiKeyParts[1];
            $key = $apiKeyParts[2];

            if($namespace != "lt") {
                error_log("Unknown namespace for API request");
                return false;
            }

            $apiUser = $this->apiRepository->getAPIKeyUser($user);

            if(password_verify($key, $apiUser['password'])){
                return $apiUser;
            }

            return false;

        }


}
