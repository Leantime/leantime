<?php

namespace kamermans\OAuth2\Token;

interface Serializable
{
    /**
     * Serialize object.
     *
     * @return array
     */
    public function serialize();
    /**
     * Unserialize object.
     *
     * @param array $data
     */
    public function unserialize(array $data);
}
