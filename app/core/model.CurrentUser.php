<?php

namespace core\models;

class CurrentUser {

    public int $id;
    public string $firstname;
    public string $lastname;
    public string $mail;
    public int $clientId;
    public bool $twoFAEnabled;
    public string $twoFASecret;
    public bool $twoFAVerified;
    public string $role;
    public int $profileId;
    public array $settings;
    public bool $isLdap;

}
