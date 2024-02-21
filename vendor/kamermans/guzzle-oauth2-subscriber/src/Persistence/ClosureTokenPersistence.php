<?php

namespace kamermans\OAuth2\Persistence;

use Illuminate\Contracts\Cache\Repository;
use kamermans\OAuth2\Token\TokenInterface;

class ClosureTokenPersistence implements TokenPersistenceInterface
{

    private $doSaveToken;
    private $doRestoreToken;
    private $doDeleteToken;
    private $doHasToken;

    public function __construct(callable $saveToken, callable $restoreToken, callable $deleteToken, callable $hasToken)
    {
        $this->doSaveToken = $saveToken;
        $this->doRestoreToken = $restoreToken;
        $this->doDeleteToken = $deleteToken;
        $this->doHasToken = $hasToken;
    }

    public function saveToken(TokenInterface $token)
    {
        call_user_func($this->doSaveToken, $token->serialize());
    }

    public function restoreToken(TokenInterface $token)
    {
        $data = call_user_func($this->doRestoreToken);

        if (!is_array($data)) {
            return null;
        }

        return $token->unserialize($data);
    }

    public function deleteToken()
    {
        call_user_func($this->doDeleteToken);
    }

    public function hasToken()
    {
        return call_user_func($this->doHasToken);
    }
}
