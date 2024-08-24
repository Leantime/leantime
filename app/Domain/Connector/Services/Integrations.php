<?php

namespace Leantime\Domain\Connector\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;

    /**
     *
     */
    class Integrations
    {
        private IntegrationsRepo $integrationRepo;

        /**
         * @param IntegrationsRepo $integrationRepo
         */
        public function __construct(IntegrationsRepo $integrationRepo)
        {
            $this->integrationRepo = $integrationRepo;
        }

        /**
         * @param int $id
         * @return object|array|false
         * @throws BindingResolutionException
         * @throws \ReflectionException
         */
        public function get(int $id): object|array|false
        {
            return $this->integrationRepo->get($id);
        }

        /**
         * @param object|array $object
         * @return bool
         */
        public function updateTicket(object|array $object): bool
        {
            // TODO: Implement update() method.
            return false;
        }

        /**
         * @param object|array $object
         * @return int|false
         * @throws \ReflectionException
         * @throws \ReflectionException
         */
        public function create(object|array $object): int|false
        {
            return $this->integrationRepo->insert($object);
        }

        /**
         * @param int $id
         * @return bool
         */
        public function delete(int $id): bool
        {
            // TODO: Implement delete() method.
            return false;
        }

        /**
         * @param array|null $searchparams
         * @return array|false
         */
        public function getAll(array $searchparams = null): array|false
        {
            // TODO: Implement getAll() method.
            return false;
        }

        /**
         * @param int   $id
         * @param array $params
         * @return bool
         */
        public function patch(int $id, array $params): bool
        {
            return $this->integrationRepo->patch($id, $params);
        }
    }

}
