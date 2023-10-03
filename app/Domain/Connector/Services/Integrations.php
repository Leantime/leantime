<?php

namespace Leantime\Domain\Connector\Services {

    use Leantime\Core\Service;
    use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;

    /**
     *
     */

    /**
     *
     */
    class Integrations implements Service
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
         * @param integer $id
         * @return object|array|false
         */
        public function get(int $id): object|array|false
        {
            return $this->integrationRepo->get($id);
        }

        /**
         * @param object|array $object
         * @return boolean
         */
        public function update(object|array $object): bool
        {
            // TODO: Implement update() method.
        }

        /**
         * @param object|array $object
         * @return integer|false
         */
        public function create(object|array $object): int|false
        {
            return $this->integrationRepo->insert($object);
        }

        /**
         * @param integer $id
         * @return boolean
         */
        public function delete(int $id): bool
        {
            // TODO: Implement delete() method.
        }

        /**
         * @param array|null $searchparams
         * @return array|false
         */
        public function getAll(array $searchparams = null): array|false
        {
            // TODO: Implement getAll() method.
        }

        /**
         * @param integer $id
         * @param array   $params
         * @return boolean
         */
        public function patch(int $id, array $params): bool
        {
            return $this->integrationRepo->patch($id, $params);
        }
    }

}
