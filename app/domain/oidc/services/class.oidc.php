<?php

namespace leantime\domain\services;

//This class handles authentication via OpenID Connect (OIDC)

use leantime\core\environment;
use GuzzleHttp\Client;
use leantime\core\frontcontroller;
use leantime\domain\services;
use leantime\domain\repositories;

class oidc {

    private static ?self $instance = null;

    private environment $config;
    private services\auth $authService;
    public repositories\users $userRepo;

    private string $providerUrl;
    private string $clientId;
    private string $clientSecret;

    private bool $configLoaded = false;
    private string $authUrl;
    private string $tokenUrl;
    private string $jwksUrl;

    public static function getInstance($sessionid = ""): static
        {

            if (self::$instance === null) {
                self::$instance = new self($sessionid);
            }

            return self::$instance;
        }

    public function __construct() {
        $this->config = environment::getInstance();
        $this->providerUrl = $this->trimTrailingSlash($this->config->oidcProviderUrl);
        $this->clientId = $this->config->oidcClientId;
        $this->clientSecret = $this->config->oidcClientSecret;
        $this->authUrl = $this->config->oidcAuthUrl;
        $this->tokenUrl = $this->config->oidcTokenUrl;
        $this->jwksUrl = $this->config->oidcJwksUrl;


        $this->authService = services\auth::getInstance();
        $this->userRepo = new repositories\users();
    }

    private function trimTrailingSlash(string $str): string {
        $almost = strlen($str)-1;
        if($str[$almost] == '/') {
            return substr($str, 0, $almost);
        }
        return $str;
    }

    public function buildLoginUrl(): string {
        return $this->getAuthUrl() . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->buildRedirectUrl(),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $this->generateState()
        ]);
    }

    private function getAuthUrl(): string {
        $this->loadEndpoints();
        return $this->authUrl;
    }

    public function callback(string $code) {
        $tokens = $this->requestTokens($code);
        $idToken = $this->decodeJWT($tokens['id_token']);
        if($idToken != null) {
            $this->login($idToken);
        } else {
            //TODO: invalid token
        }
    }

    private function login(array $idToken): void {
        // echo '<pre>' . print_r($idToken, true) . '</pre>';
        // return;
        $userName = $idToken['email'];
        $user = $this->userRepo->getUserByEmail($userName);

        if($user === false) {
            //create user if it doesn't exist yet
            $userArray = [
                'firstname' => $idToken['given_name'],
                'lastname' => $idToken['family_name'],
                'phone' => '',
                'user' => $userName,
                'role' => $this->getUserRole($idToken),
                'password' => '',
                'clientId' => '',
                'source' => 'oidc',
                'status' => 'a'
            ];
            $userId = $this->userRepo->addUser($userArray);

            if ($userId !== false) {
                $user = $this->userRepo->getUserByEmail($userName);
            } else {
                error_log("OIDC user creation failed.");
                return;
            }
        } else {
            //update user if it exists
            $user['firstname'] = $idToken['given_name'];
            $user['lastname'] = $idToken['family_name'];
            $user['role'] = $this->getUserRole($idToken, $user);

            $this->userRepo->editUser($user, $user['id']);
        }

        $this->authService->setUserSession($user, false);

        frontcontroller::redirect(BASE_URL . "/dashboard/home");
    }

    private function getUserRole(array $idToken, array $user = []): string {
        return $user['role'] ?? 'reader';
    }

    private function requestTokens(string $code) {
        $httpClient = new Client();
        $response = $httpClient->post($this->getTokenUrl(), [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->buildRedirectUrl(),
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    private function decodeJWT(string $jwt): array|null {
        list($header, $content, $signature) = explode('.', $jwt);

        $tokenData = json_decode($this->decodeBase64Url($content), true);

        if($this->trimTrailingSlash($tokenData['iss']) != $this->providerUrl) {
            return null;
        }

        $key = $this->getPublicKey();

        if($key === false) {
            return null;
        }

        $data = $header . '.' . $content;

        if(openssl_verify($data, $this->decodeBase64Url($signature), $key, $this->getAlgorythm($header)) === 1) {
            return $tokenData;
        }
        return null;
    }

    private function getAlgorythm(string $header): int {
        $algorythmName = json_decode($this->decodeBase64Url($header), true)['alg'];
        $map = [
            'RS256' => OPENSSL_ALGO_SHA256
        ];
        return $map[$algorythmName];
    }

    private function getPublicKey(): \OpenSSLAsymmetricKey|false {
        $httpClient = new Client();
        $response = $httpClient->get($this->getJwksUrl());
        $keys = json_decode($response->getBody()->getContents(), true)['keys'];
        return openssl_pkey_get_public('-----BEGIN CERTIFICATE-----' . PHP_EOL . implode(PHP_EOL, str_split($keys[0]['x5c'][0], 64)) . PHP_EOL . '-----END CERTIFICATE-----');
    }

    private function getJwksUrl(): string {
        $this->loadEndpoints();
        return $this->jwksUrl;
    }

    private function getTokenUrl(): string {
        $this->loadEndpoints();
        return $this->tokenUrl;
    }

    private function loadEndpoints(): void {
        if($this->configLoaded) {
            return;
        }
        $httpClient = new Client();
        $response = $httpClient->get($this->providerUrl . '/.well-known/openid-configuration');
        $endpoints = json_decode($response->getBody()->getContents(), true);

        //load all not yet defined endpoints from well-known configuration

        if(!$this->authUrl) {
            $this->authUrl = $endpoints['authorization_endpoint'];
        }

        if(!$this->tokenUrl) {
            $this->tokenUrl = $endpoints['token_endpoint'];
        }

        if(!$this->jwksUrl) {
            $this->jwksUrl = $endpoints['jwks_uri'];
        }
    }

    private function buildRedirectUrl(): string {
        return $this->trimTrailingSlash(BASE_URL) . '/oidc/callback';
    }

    private function generateState(): string {
        return bin2hex(random_bytes(16));
    }

    private function verifyState(string $state): bool {
        //TODO
        return true;
    }

    private function encodeBase64Url(string $value): string {
        return strtr(base64_encode($value),'+/', '-_');
    }

    private function decodeBase64Url(string $value): string {
        return base64_decode(strtr($value, '-_', '+/'));
    }
}