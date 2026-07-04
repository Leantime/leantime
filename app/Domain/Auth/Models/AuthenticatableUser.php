<?php

namespace Leantime\Domain\Auth\Models;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Lightweight Authenticatable wrapper around a user-data row.
 *
 * Replaces the `(object) $userRow` stdClass casts in AuthUser/ApiGuard so the provider/guard
 * methods satisfy their `?Authenticatable` contracts. It uses dynamic properties on purpose so it
 * stays a behavioural drop-in for the old stdClass cast — same property reads, same json/array
 * serialization, truthy even when empty — and merely ADDS the Authenticatable accessor methods.
 */
#[\AllowDynamicProperties]
class AuthenticatableUser implements Authenticatable
{
    /**
     * @param  array<string, mixed>  $attributes  A user row (column => value).
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id ?? null;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return $this->password ?? '';
    }

    public function getRememberToken(): string
    {
        return $this->remember_token ?? '';
    }

    public function setRememberToken($value): void
    {
        // No-op: Leantime does not persist remember tokens (mirrors Auth::setRememberToken and
        // AuthUser::updateRememberToken, which are likewise not implemented).
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
