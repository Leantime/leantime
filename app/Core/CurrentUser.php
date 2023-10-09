<?php

namespace Leantime\Core;

/**
 * Model for the CurrentUser
 *
 * @package    leantime
 * @subpackage core
 */
class CurrentUser
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $firstname;

    /**
     * @var string
     */
    public string $lastname;

    /**
     * @var string
     */
    public string $mail;

    /**
     * @var int
     */
    public int $clientId;

    /**
     * @var bool
     */
    public bool $twoFAEnabled;

    /**
     * @var string
     */
    public string $twoFASecret;

    /**
     * @var bool
     */
    public bool $twoFAVerified;

    /**
     * @var string
     */
    public string $role;

    /**
     * @var int
     */
    public int $profileId;

    /**
     * @var array
     */
    public array $settings;

    /**
     * @var bool
     */
    public bool $isLdap;
}
