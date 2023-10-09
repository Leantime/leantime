<?php

namespace Leantime\Domain\Entityrelations\Services {

    use Leantime\Domain\Entityrelations\Repositories\Entityrelations as EntityrelationRepository;

    /**
     *
     */
    class Entityrelations
    {
        private EntityrelationRepository $entityRelationshipsRepo;

        /**
         * @param EntityrelationRepository $entityRelationshipsRepo
         */
        public function __construct(
            EntityrelationRepository $entityRelationshipsRepo
        ) {
            $this->entityRelationshipsRepo = $entityRelationshipsRepo;
        }

        /**
         * @param $entityA
         * @param $entityAType
         * @param $relationship
         * @param $entityB
         * @param $entityBType
         * @param $meta
         * @return mixed
         */
        /**
         * @param $entityA
         * @param $entityAType
         * @param $relationship
         * @param $entityB
         * @param $entityBType
         * @param string       $meta
         * @return mixed
         */
        public function saveRelationship($entityA, $entityAType, $relationship, $entityB, $entityBType, string $meta = ""): mixed
        {
            return $this->settingsRepo->saveSetting($entityA, $entityAType, $relationship, $entityB, $entityBType, $meta = "");
        }

        /**
         * @param string $entitySide
         * @param int $entity
         * @param string $entityType
         * @param string $relationship
         * @return mixed
         */
        /**
         * @param string $entitySide
         * @param int    $entity
         * @param string $entityType
         * @param string $relationship
         * @return mixed
         */
        public function getRelationshipByEntity(string $entitySide, int $entity, string $entityType, string $relationship): mixed
        {
            return $this->settingsRepo->getSetting($entitySide, $entity, $entityType, $relationship);
        }
    }

}
