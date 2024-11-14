<?php

namespace Leantime\Domain\Auth\Models {

    use Carbon\CarbonImmutable;

    class CurrentUser
    {
        public function __construct(
            public int $id,
            public string $name,
            public string $profileId,
            public string $mail,
            public int $clientId,
            public string $role,
            public mixed $settings,
            public bool $twoFAEnabled,
            public bool $twoFAVerified,
            public string $twoFASecret,
            public bool $isLdap,
            public CarbonImmutable $createdOn,
            public CarbonImmutable $modified,
        ) {}
    }

}
