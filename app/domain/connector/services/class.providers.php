<?php

namespace leantime\domain\services\connector {

    use leantime\core\eventhelpers;
    use leantime\domain\models\connector\entity;
    use leantime\domain\models\connector\provider;
    use PHPMailer\PHPMailer\Exception;

    class providers
    {

        use eventhelpers;

        private $providers = [];

       public function __construct()
       {
           $this->loadProviders();


       }

       public function loadProviders () {

           //providerId => provider
           $this->providers = self::dispatch_filter('providerList', [

           ]);
       }

       public function getProviders(){
            return $this->providers;
       }

       public function getProvider($providerId): provider
       {
           if (isset($this->providers[$providerId])) {
               return $this->providers[$providerId];
           } else {
               throw new \Exception("Provider does not exist");
           }

       }



    }

}
