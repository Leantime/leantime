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
}
