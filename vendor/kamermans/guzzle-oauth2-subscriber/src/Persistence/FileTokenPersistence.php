<?php

namespace kamermans\OAuth2\Persistence;

use kamermans\OAuth2\Token\TokenInterface;

class FileTokenPersistence implements TokenPersistenceInterface
{
    /**
     * @var string
     */
    private $filepath;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function saveToken(TokenInterface $token)
    {
        file_put_contents($this->filepath, json_encode($token->serialize()), LOCK_EX);
    }

    public function restoreToken(TokenInterface $token)
    {
        if (!file_exists($this->filepath)) {
            return null;
        }

        $data = @json_decode(@file_get_contents($this->filepath), true);

        if (!is_array($data)) {
            return null;
        }

        return $token->unserialize($data);
    }

    public function deleteToken()
    {
        if (file_exists($this->filepath)) {
            @unlink($this->filepath);
        }
    }

    public function hasToken()
    {
        return file_exists($this->filepath);
    }
}
