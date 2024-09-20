<?php

namespace Leantime\Domain\Entityrelations\Services;

use Leantime\Domain\Entityrelations\Repositories\Entityrelations as EntityrelationRepository;
use Leantime\Domain\Setting\Repositories\Setting;

class Entityrelations
{
    /**
     * Class constructor.
     *
     * @param EntityrelationRepository $entityRelationshipsRepo The entity relationships repository.
     * @param Setting                  $settingsRepo            The settings repository.
     *
     * @return void
     */
    public function __construct(
        private EntityrelationRepository $entityRelationshipsRepo,
        private Setting $settingsRepo
    ) {
    }

    /**
     * @param        $entityA
     * @param        $entityAType
     * @param        $relationship
     * @param        $entityB
     * @param        $entityBType
     * @param string $meta
     *
     * @return mixed
     */
    public function saveRelationship($entityA, $entityAType, $relationship, $entityB, $entityBType, string $meta = ''): mixed
    {
        return $this->settingsRepo->saveSetting($entityA, $entityAType, $relationship, $entityB, $entityBType, $meta = '');
    }

    /**
     * @param string $entitySide
     * @param int    $entity
     * @param string $entityType
     * @param string $relationship
     *
     * @return mixed
     */
    public function getRelationshipByEntity(string $entitySide, int $entity, string $entityType, string $relationship): mixed
    {
        return $this->settingsRepo->getSetting($entitySide, $entity, $entityType, $relationship);
    }
}
