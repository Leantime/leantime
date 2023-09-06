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
     * @var integer
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
     * @var integer
     */
    public int $clientId;

    /**
     * @var boolean
     */
    public bool $twoFAEnabled;

    /**
     * @var string
     */
    public string $twoFASecret;

    /**
     * @var boolean
     */
    public bool $twoFAVerified;

    /**
     * @var string
     */
    public string $role;

    /**
     * @var integer
     */
    public int $profileId;

    /**
     * @var array
     */
    public array $settings;

    /**
     * @var boolean
     */
    public bool $isLdap;
}
