<?php

namespace leantime\domain\services;

//This class handles authentication via OpenID Connect (OIDC)

use leantime\core\environment;
use GuzzleHttp\Client;
use leantime\core\frontcontroller;
use leantime\core\language;
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

    private language $language;

    public static function getInstance($sessionid = ""): static
        {

            if (self::$instance === null) {
                self::$instance = new self($sessionid);
            }

            return self::$instance;
        }

    public function __construct() {
        $this->config = environment::getInstance();
        $this->language = language::getInstance();
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
            $this->displayError('oidc.error.invalidToken');
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
                'lastname' => $idToken['family_name'] ?? '',
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
            $user['lastname'] = $idToken['family_name'] ?? '';
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
            $this->displayError('oidc.error.providerMismatch', $tokenData['iss'], $this->providerUrl);
        }

        $headerData = json_decode($this->decodeBase64Url($header), true);


        $key = $this->getPublicKey($headerData['kid']);

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

        if(!isset($map[$algorythmName])) {
            $this->displayError("oidc.error.unsupportedAlgorythm", $algorythmName);
        }

        
        return $map[$algorythmName];
    }

    private function getPublicKey(string $kid): \OpenSSLAsymmetricKey {
        $httpClient = new Client();
        $response = $httpClient->get($this->getJwksUrl());
        $keys = json_decode($response->getBody()->getContents(), true);
        if(isset($keys['keys'])) {
            $keys = $keys['keys'];
        }

        foreach($keys as $possibleKid => $key) {
            $keySource = '';

            if(is_string($possibleKid)) {
                //old format like https://www.googleapis.com/oauth2/v1/certs
                if($possibleKid == $kid) {
                    $keySource = $key;
                }
            }
            else if(!isset($kid[0]) || $kid == $key['kid']) {
                $keySource = '';
                if(isset($key['x5c'])) {
                    $keySource = '-----BEGIN CERTIFICATE-----' . PHP_EOL . chunk_split( $key['x5c'][0], 64, PHP_EOL) . PHP_EOL . '-----END CERTIFICATE-----';
                }
                else if(isset($key['n'])) {
                    $this->displayError('oidc.error.unsupportedKeyFormat');
                }
            }

            if($keySource) {
                return openssl_pkey_get_public($keySource);
            }
        }

        return false;
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

        if($this->authUrl && $this->tokenUrl && $this->jwksUrl) {
            $this->configLoaded = true;
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

    private function displayError(string $translationKey, string ...$values): void {
        die(sprintf($this->language->__($translationKey), ...$values));
    }
}