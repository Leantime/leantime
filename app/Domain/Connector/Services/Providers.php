<?php

namespace Leantime\Domain\Connector\Services {

    use Leantime\Core\Events\DispatchesEvents;
    use Leantime\Domain\Connector\Models\Provider;

    class Providers
    {
        use DispatchesEvents;

        private array $providers = [];

        public function __construct()
        {
            $this->loadProviders();
        }

        public function loadProviders(): void
        {

            //Default Providers
            $provider = app()->make(\Leantime\Domain\CsvImport\Services\CsvImport::class);
            $this->providers[$provider->id] = $provider;

            //providerId => provider
            $this->providers = self::dispatch_filter('providerList', $this->providers);
        }

        public function getProviders(): array
        {
            return $this->providers;
        }

        /**
         * @throws \Exception
         */
        public function getProvider($providerId): provider
        {
            if (isset($this->providers[$providerId])) {
                return $this->providers[$providerId];
            } else {
                throw new \Exception('Provider does not exist');
            }
        }
    }

}
