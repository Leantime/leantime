<?php

namespace Leantime\Core\Mcp\Auth;

class McpPrincipal
{
    public function __construct(
        public readonly int $userId,
        public readonly int $roleId,
        public readonly string $role,
        public readonly int $accessTokenId,
        public readonly string $tokenName,
        public readonly array $abilities,
        public readonly ?int $agentId = null,
        public readonly array $user = [],
    ) {}

    public function can(string $ability): bool
    {
        if (in_array('*', $this->abilities, true)) {
            return true;
        }

        if (in_array($ability, $this->abilities, true)) {
            return true;
        }

        foreach ($this->abilities as $grantedAbility) {
            if (str_ends_with($grantedAbility, ':*')) {
                $prefix = substr($grantedAbility, 0, -1);
                if (str_starts_with($ability, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
