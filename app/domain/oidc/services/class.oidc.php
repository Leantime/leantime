<?php

namespace leantime\domain\services;

//This class handles authentication via OpenID Connect (OIDC)

use leantime\core\environment;
use GuzzleHttp\Client;

class oidc {

    private static ?self $instance = null;

    private environment $config;

    private string $providerUrl;
    private string $clientId;
    private string $clientSecret;

    private bool $configLoaded = false;
    private string $authUrl;
    private string $tokenUrl;

    public static function getInstance($sessionid = ""): static
        {

            if (self::$instance === null) {
                self::$instance = new self($sessionid);
            }

            return self::$instance;
        }

    public function __construct() {
        $this->config = environment::getInstance();
        $this->providerUrl = $this->trimTrailingSlash($this->config->OidcProviderUrl);
        $this->clientId = $this->config->OidcClientId;
        $this->clientSecret = $this->config->OidcClientSecret;
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
        $accessToken = $this->decodeJWT($tokens['access_token']);
        echo '<pre>' . print_r($accessToken, true) . '</pre>';
        $idToken = $this->decodeJWT($tokens['id_token']);
        echo '<pre>' . print_r($idToken, true) . '</pre>';
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
        echo '<pre>' . print_r($code, true) . '</pre>';
        return json_decode($response->getBody()->getContents(), true);
    }

    private function decodeJWT(string $jwt): array|null {
        list($header, $content, $signature) = explode('.', $jwt);

        $tokenData = json_decode($this->decodeBase64Url($content), true);

        if($this->trimTrailingSlash($tokenData['iss']) != $this->providerUrl) {
            return null;
        }

        return $tokenData;
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
        $this->authUrl = $endpoints['authorization_endpoint'];
        $this->tokenUrl = $endpoints['token_endpoint'];
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