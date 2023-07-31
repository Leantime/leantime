<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class entityrelations
    {
        private repositories\entityrelations $entityRelationshipsRepo;

        public function __construct(
            repositories\entityrelations $entityRelationshipsRepo
        ) {
            $this->entityRelationshipsRepo = $entityRelationshipsRepo;
        }

        public function saveRelationship($entityA, $entityAType, $relationship, $entityB, $entityBType, $meta = ""){
            return $this->settingsRepo->saveSetting($entityA, $entityAType, $relationship, $entityB, $entityBType, $meta = "");
        }

        public function getRelationshipByEntity(string $entitySide, int $entity, string $entityType, string $relationship){
            return $this->settingsRepo->getSetting($entitySide, $entity, $entityType, $relationship);
        }
    }

}
