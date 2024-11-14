<?php

namespace Leantime\Domain\Connector\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;

    class Integrations
    {
        private IntegrationsRepo $integrationRepo;

        public function __construct(IntegrationsRepo $integrationRepo)
        {
            $this->integrationRepo = $integrationRepo;
        }

        /**
         * @throws BindingResolutionException
         * @throws \ReflectionException
         */
        public function get(int $id): object|array|false
        {
            return $this->integrationRepo->get($id);
        }

        public function updateTicket(object|array $object): bool
        {
            // TODO: Implement update() method.
            return false;
        }

        /**
         * @throws \ReflectionException
         * @throws \ReflectionException
         */
        public function create(object|array $object): int|false
        {
            return $this->integrationRepo->insert($object);
        }

        public function delete(int $id): bool
        {
            // TODO: Implement delete() method.
            return false;
        }

        public function getAll(?array $searchparams = null): array|false
        {
            // TODO: Implement getAll() method.
            return false;
        }

        public function patch(int $id, array $params): bool
        {
            return $this->integrationRepo->patch($id, $params);
        }
    }

}
