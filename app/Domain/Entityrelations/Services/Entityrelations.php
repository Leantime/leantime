<?php

namespace Leantime\Domain\Entityrelations\Services {

    use Leantime\Domain\Entityrelations\Repositories\Entityrelations as EntityrelationRepository;
    class Entityrelations
    {
        private EntityrelationRepository $entityRelationshipsRepo;

        public function __construct(
            EntityrelationRepository $entityRelationshipsRepo
        ) {
            $this->entityRelationshipsRepo = $entityRelationshipsRepo;
        }

        public function saveRelationship($entityA, $entityAType, $relationship, $entityB, $entityBType, $meta = "")
        {
            return $this->settingsRepo->saveSetting($entityA, $entityAType, $relationship, $entityB, $entityBType, $meta = "");
        }

        public function getRelationshipByEntity(string $entitySide, int $entity, string $entityType, string $relationship)
        {
            return $this->settingsRepo->getSetting($entitySide, $entity, $entityType, $relationship);
        }
    }

}
