<?php

namespace Leantime\Domain\Connector\Services {

    use Leantime\Core\Service;
    use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;

    class Integrations implements Service
    {
        private IntegrationsRepo $integrationRepo;

        public function __construct(IntegrationsRepo $integrationRepo)
        {
            $this->integrationRepo = $integrationRepo;
        }

        public function get(int $id): object|array|false
        {
            return $this->integrationRepo->get($id);
        }

        public function update(object|array $object): bool
        {
            // TODO: Implement update() method.
        }

        public function create(object|array $object): int|false
        {
            return $this->integrationRepo->insert($object);
        }

        public function delete(int $id): bool
        {
            // TODO: Implement delete() method.
        }

        public function getAll(array $searchparams = null): array|false
        {
            // TODO: Implement getAll() method.
        }

        public function patch(int $id, array $params): bool
        {
            return $this->integrationRepo->patch($id, $params);
        }
    }

}
