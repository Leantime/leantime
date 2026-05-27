<?php

namespace Leantime\Domain\Auth\Services;

use DateTimeInterface;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Sanctum\Contracts\HasAbilities;
use Laravel\Sanctum\HasApiTokens;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;

class AccessToken implements HasAbilities
{
    use HasApiTokens, \Illuminate\Auth\Authenticatable;

    public ?int $id = null;

    public string $tokenableType;

    public int $tokenableId;

    public string $name;

    public string $token;

    public array $abilities;

    public ?DateTimeInterface $lastUsedAt;

    public ?DateTimeInterface $expires_at;

    public ?DateTimeInterface $created_at;

    public ?DateTimeInterface $updatedAt;

    public AuthUser $tokenable;

    protected AccessTokenRepository $tokenRepo;

    public function __construct(
        array $attributes = [],
    ) {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->tokenRepo = app()->make(AccessTokenRepository::class);
        $this->tokenable = app()->make(AuthUser::class);

        $this->abilities = $this->abilities ?? ['*'];
    }

    public function can($ability): bool
    {
        return in_array('*', $this->abilities) ||
               array_key_exists($ability, array_flip($this->abilities));
    }

    public function cant($ability): bool
    {
        return ! $this->can($ability);
    }

    public function createToken($userId, $name = null)
    {

        if ($userId == session('userdata.id') || Auth::userIsAtLeast(Roles::$admin)) {

            $token = $this->tokenRepo->createToken($userId, $name ?? 'personal-token');

            return (object) $token;

        } else {
            return false;
        }
    }

    public static function findToken($token)
    {
        $tokenObject = new self;
        $tokenData = $tokenObject->tokenRepo->findToken($token);

        if (empty($tokenData)) {
            return false;
        }

        $tokenObject->id = $tokenData['id'];
        $tokenObject->expires_at = ! empty($tokenData['expires_at']) ? dtHelper()->parseDbDateTime($tokenData['expires_at']) : null;
        $tokenObject->created_at = ! empty($tokenData['created_at']) ? dtHelper()->parseDbDateTime($tokenData['created_at']) : null;

        $tokenObject->tokenable->setUser($tokenData['tokenable_id']);

        return $tokenObject;
    }

    public function getConnection()
    {
        return '';
    }

    public function forceFill()
    {
        $this->tokenRepo->updateLastUsedAt($this->id);

        return $this;
    }

    public function save()
    {
        return true;
    }

    public function getUserTokens(int $userId)
    {

        if (Auth::userIsAtLeast(Roles::$admin) || $userId == session('userdata.id')) {

            return $this->tokenRepo->getAllTokensByUserId($userId) ?? [];

        } else {

            throw new UnauthorizedException('You are not authorized to access this resource.');
        }
    }

    public function getTokenById($tokenId)
    {
        return $this->tokenRepo->findTokenById($tokenId);
    }

    public function deleteToken(int $tokenId)
    {

        $token = $this->getTokenById($tokenId);

        if (Auth::userIsAtLeast(Roles::$admin) || $token['tokenable_id'] == session('userdata.id')) {

            return $this->tokenRepo->deleteToken($tokenId);

        } else {

            throw new UnauthorizedException('You are not authorized to access this resource.');
        }
    }

    /**
     * Revoke the bearer token used to authenticate the current request.
     * Designed for client-side sign-out flows (mobile, third-party
     * integrations) that need a server-side invalidation rather than
     * just clearing local credentials.
     *
     * Uses the request's Authorization header to identify the token —
     * caller doesn't need to track its own token id. Defense-in-depth
     * check confirms the matched token belongs to the session user
     * (the middleware should already guarantee this since the bearer
     * is what populated the session, but the explicit check guards
     * against any odd state).
     *
     * @api
     */
    public function revokeCurrentToken(): bool
    {
        $request = app(\Leantime\Core\Http\ApiRequest::class);
        $bearer = $request->getBearerToken();
        if (! $bearer) {
            return false;
        }

        $token = $this->tokenRepo->findToken($bearer);
        if (! $token) {
            return false;
        }

        $sessionUserId = (int) session('userdata.id');
        if ($sessionUserId === 0 || (int) $token['tokenable_id'] !== $sessionUserId) {
            // Bearer matched a row that isn't this session's user —
            // refuse rather than risk deleting someone else's token.
            return false;
        }

        return $this->tokenRepo->deleteToken((int) $token['id']);
    }
}
