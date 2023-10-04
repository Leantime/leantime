<?php

namespace Leantime\Domain\Connector\Services {

    use Leantime\Core\Eventhelpers;
    use Leantime\Domain\Connector\Models\Entity;
    use Leantime\Domain\Connector\Models\Provider;
    use PHPMailer\PHPMailer\Exception;

    /**
     *
     */
    class Providers
    {
        use Eventhelpers;

        private array $providers = [];

        public function __construct()
        {
            $this->loadProviders();
        }

        /**
         * @return void
         */
        public function loadProviders(): void
        {

            //providerId => provider
            $this->providers = self::dispatch_filter('providerList', []);
        }

        /**
         * @return array
         */
        /**
         * @return array
         */
        public function getProviders(): array
        {
            return $this->providers;
        }

        /**
         * @param $providerId
         * @return Provider
         * @throws \Exception
         */
        /**
         * @param $providerId
         * @return Provider
         * @throws \Exception
         */
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
