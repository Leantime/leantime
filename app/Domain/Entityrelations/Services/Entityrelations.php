<?php

namespace Leantime\Domain\Entityrelations\Services;

use Leantime\Domain\Entityrelations\Repositories\Entityrelations as EntityrelationRepository;
use Leantime\Domain\Setting\Repositories\Setting;

class Entityrelations
{
    /**
     * Class constructor.
     *
     * @param  EntityrelationRepository  $entityRelationshipsRepo  The entity relationships repository.
     * @param  Setting  $settingsRepo  The settings repository.
     * @return void
     */
    public function __construct(
        private EntityrelationRepository $entityRelationshipsRepo,
        private Setting $settingsRepo
    ) {}

    public function saveRelationship($entityA, $entityAType, $relationship, $entityB, $entityBType, string $meta = ''): mixed
    {
        // FIXME(phpstan-l2): this method is broken. Setting::saveSetting() is (string $type,
        // mixed $value) — only the first two args were ever persisted; $relationship/$entityB/
        // $entityBType/$meta were silently dropped by PHP. The injected EntityrelationRepository
        // ($entityRelationshipsRepo) also has no matching 6-arg method. Trimmed to preserve
        // current runtime behavior; the relationship-storage design needs to be rebuilt.
        return $this->settingsRepo->saveSetting($entityA, $entityAType);
    }

    public function getRelationshipByEntity(string $entitySide, int $entity, string $entityType, string $relationship): mixed
    {
        // FIXME(phpstan-l2): broken counterpart of saveRelationship() — Setting::getSetting() is
        // (string $type, mixed $default = false); the extra args were silently dropped. Trimmed
        // to preserve current runtime behavior. Needs a real entity-relationship store.
        return $this->settingsRepo->getSetting($entitySide, $entity);
    }
}
