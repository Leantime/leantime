<?php

namespace Leantime\Domain\Connector\Services {

    use Leantime\Core\Eventhelpers;
    use Leantime\Domain\Connector\Models\Entity;
    use Leantime\Domain\Connector\Models\Provider;
    use PHPMailer\PHPMailer\Exception;

    class Providers
    {
        use Eventhelpers;

        private $providers = [];

        public function __construct()
        {
            $this->loadProviders();
        }

        public function loadProviders()
        {

            //providerId => provider
            $this->providers = self::dispatch_filter('providerList', []);
        }

        public function getProviders()
        {
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
