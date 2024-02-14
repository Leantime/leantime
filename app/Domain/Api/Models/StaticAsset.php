<?php

namespace Leantime\Domain\Api\Models;

use Leantime\Domain\Api\Contracts\StaticAssetType;

/**
 * Represents a static asset file.
 */
class StaticAsset
{
    /**
     * @param string          $key
     * @param string          $absPath
     * @param StaticAssetType $fileType
     */
    public function __construct(
        public string $key,
        public string $absPath,
        public StaticAssetType $fileType,
    ) {
    }
}
