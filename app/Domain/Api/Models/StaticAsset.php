<?php

namespace Leantime\Domain\Api\Models;

use Leantime\Domain\Api\Contracts\StaticAssetType;

class StaticAsset
{
    public function __construct(
        public string $key,
        public string $absPath,
        public StaticAssetType $fileType,
    ) {}
}
