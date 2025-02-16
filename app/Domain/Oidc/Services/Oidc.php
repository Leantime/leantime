<?php

namespace Leantime\Domain\Oidc\Services;

// This class Handles authentication via OpenID Connect (OIDC)

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use OpenSSLAsymmetricKey;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use Symfony\Component\HttpFoundation\Response;

class Oidc
{
    private Environment $config;

    private Language $Language;

    private AuthService $authService;

    public UserRepository $userRepo;

    private bool $configLoaded = false;

    private string $providerUrl;

    private string $autoDiscoverUrl;

    private string $clientId;

    private string $clientSecret;

    private string $authUrl;

    private string $tokenUrl;

    private string $jwksUrl;

    private string $userInfoUrl;

    private string $certificateString;

    private string $certificateFile;

    private string $scopes;

    private bool $createUser;

    private int $defaultRole; // 20 == editor

    private string $fieldEmail;

    private string $fieldFirstName;

    private string $fieldLastName;

    private string $fieldPhone;

    private string $fieldJobtitle;

    private string $fieldJoblevel;

    private string $fieldDepartment;

    private Language $language;

    public function __construct(
        Environment $config,
        Language $language,
        AuthService $authService,
        UserRepository $userRepo
    ) {
        $this->config = $config;
        $this->authService = $authService;
        $this->userRepo = $userRepo;
        $this->language = $language;

        $providerUrl = $this->config->get('oidcProviderUrl');

        $this->providerUrl = ! empty($providerUrl) ? $this->trimTrailingSlash($providerUrl) : $providerUrl;
        $this->autoDiscoverUrl = $this->config->get('oidcAutoDiscoverUrl', '');
        $this->clientId = $this->config->get('oidcClientId', '');
        $this->clientSecret = $this->config->get('oidcClientSecret', '');
        $this->authUrl = $this->config->get('oidcAuthUrl', '');
        $this->tokenUrl = $this->config->get('oidcTokenUrl', '');
        $this->jwksUrl = $this->config->get('oidcJwksUrl', '');
        $this->userInfoUrl = $this->config->get('oidcUserInfoUrl', '');
        $this->certificateString = $this->config->get('oidcCertificateString', '');
        $this->certificateFile = $this->config->get('oidcCertificateFile', '');
        $this->scopes = $this->config->get('oidcScopes', '');
        $this->createUser = $this->config->get('oidcCreateUser', false);
        $this->defaultRole = $this->config->get('oidcDefaultRole', 20);

        $this->fieldEmail = $this->config->get('oidcFieldEmail', '');
        $this->fieldFirstName = $this->config->get('oidcFieldFirstName', '');
        $this->fieldLastName = $this->config->get('oidcFieldLastName', '');
        $this->fieldPhone = $this->config->get('oidcFieldPhone', '');
        $this->fieldJobtitle = $this->config->get('oidcFieldJobtitle', '');
        $this->fieldJoblevel = $this->config->get('oidcFieldJoblevel', '');
        $this->fieldDepartment = $this->config->get('oidcFieldDepartment', '');
    }

    private function trimTrailingSlash(string $str): string
    {
        $almost = strlen($str) - 1;
        if ($str[$almost] === '/') {
            return substr($str, 0, $almost);
        }

        return $str;
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function buildLoginUrl(): string
    {

        if ($this->getAuthUrl()) {

            return $this->getAuthUrl().'?'.http_build_query([
                'client_id' => $this->clientId,
                'redirect_uri' => $this->buildRedirectUrl(),
                'response_type' => 'code',
                'scope' => $this->scopes,
                'state' => $this->generateState(),
            ]);
        }

        return false;
    }

    /**
     * @throws GuzzleException
     */
    private function getAuthUrl(): string
    {
        if (! empty($this->authUrl || $this->loadEndpoints())) {
            return $this->authUrl;
        }

        return false;
    }

    /**
     * @return void
     *
     * @throws GuzzleException
     */
    public function callback(string $code, string $state): Response
    {
        if (! $this->verifyState($state)) {
            $this->displayError('oidc.error.invalidState');
        }

        $tokens = $this->requestTokens($code);

        if (! is_array($tokens)) {
            $this->displayError($tokens);
        }

        $userInfo = null;
        // echo '<pre>' . print_r($tokens, true) . '</pre>';
        if (isset($tokens['id_token'])) {
            $userInfo = $this->decodeJWT($tokens['id_token']);
        } elseif (isset($tokens['access_token'])) {
            // fallback to OAuth userinfo endpoint
            $userInfo = $this->pollUserInfo($tokens['access_token']);
        } else {
            $this->displayError('oidc.error.unsupportedToken');
        }

        if ($userInfo == null) {
            // TODO: invalid token
            $this->displayError('oidc.error.invalidToken');
        }

        return $this->login($userInfo);
    }

    /**
     * @throws GuzzleException
     */
    private function pollUserInfo(string $token): array
    {
        return $this->getMultiUrl($this->userInfoUrl, $token);
    }

    /**
     * @return void
     */
    private function login(array $userInfo): Response
    {

        $userName = $this->readMultilayerKey($userInfo, $this->fieldEmail);

        if (! $userName) {
            $this->displayError('oidc.error.emailUnavailable');
        }

        $user = $this->userRepo->getUserByEmail($userName);

        if ($user === false) {
            if ($this->createUser) {
                // create user if it doesn't exist yet
                $userArray = [
                    'firstname' => $this->readMultilayerKey($userInfo, $this->fieldFirstName),
                    'lastname' => $this->readMultilayerKey($userInfo, $this->fieldLastName),
                    'phone' => $this->readMultilayerKey($userInfo, $this->fieldPhone),
                    'jobTitle' => $this->readMultilayerKey($userInfo, $this->fieldJobtitle),
                    'jobLevel' => $this->readMultilayerKey($userInfo, $this->fieldJoblevel),
                    'department' => $this->readMultilayerKey($userInfo, $this->fieldDepartment),
                    'user' => $userName,
                    'role' => $this->defaultRole,
                    'password' => '',
                    'clientId' => '',
                    'source' => 'oidc',
                    'status' => 'a',
                ];

                $userId = $this->userRepo->addUser($userArray);

                if ($userId !== false) {
                    $user = $this->userRepo->getUserByEmail($userName);
                } else {
                    throw new \Exception('OIDC user creation failed.');
                }
            } else {
                $this->displayError('oidc.error.user_not_found');
            }
        } else {
            // update user if it exists
            $user['user'] = $user['username'];
            $user['firstname'] = $this->readMultilayerKey($userInfo, $this->fieldFirstName) != '' ? $this->readMultilayerKey($userInfo, $this->fieldFirstName) : $user['firstname'];
            $user['lastname'] = $this->readMultilayerKey($userInfo, $this->fieldLastName) != '' ? $this->readMultilayerKey($userInfo, $this->fieldLastName) : $user['lastname'];
            $user['phone'] = $this->readMultilayerKey($userInfo, $this->fieldPhone) != '' ? $this->readMultilayerKey($userInfo, $this->fieldPhone) : $user['phone'];
            $user['jobTitle'] = $this->readMultilayerKey($userInfo, $this->fieldJobtitle) != '' ? $this->readMultilayerKey($userInfo, $this->fieldJobtitle) : $user['jobTitle'];
            $user['jobLevel'] = $this->readMultilayerKey($userInfo, $this->fieldJoblevel) != '' ? $this->readMultilayerKey($userInfo, $this->fieldJoblevel) : $user['jobLevel'];
            $user['department'] = $this->readMultilayerKey($userInfo, $this->fieldDepartment) != '' ? $this->readMultilayerKey($userInfo, $this->fieldDepartment) : $user['department'];

            $user['role'] = $this->getUserRole($userInfo, $user);

            $this->userRepo->editUser($user, $user['id']);

            // Get updated user
            $user = $this->userRepo->getUserByEmail($userName);
        }

        $this->authService->setUserSession($user, false);

        return Frontcontroller::redirect(BASE_URL.'/dashboard/home');
    }

    private function getUserRole(array $userInfo, array $user = []): string
    {
        return $user['role'] ?? 'readonly';
    }

    /**
     * @throws GuzzleException
     */
    private function requestTokens(string $code): array|string
    {
        $httpClient = Http::withoutVerifying();

        // Add proper client authentication headers
        $response = $httpClient->asForm()->post($this->getTokenUrl(), [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->buildRedirectUrl(),
        ]);

        if ($response->failed()) {
            Log::error('OIDC Token Request Failed: '.$response->body());
            throw new \RuntimeException('Failed to retrieve tokens: '.$response->body());
        }

        $headerArray = [];
        foreach ($response->getHeaders() as $header => $values) {
            $headerArray[strtolower($header)] = $values;
        }
        $contentType = array_pop($headerArray['content-type']);

        switch ($contentType) {
            case 'application/x-www-form-urlencoded; charset=utf-8':
            case 'application/x-www-form-urlencoded':
                $result = [];
                parse_str($response->getBody()->getContents(), $result);

                return $result;
            default:
                return json_decode($response->getBody()->getContents(), true);
        }
    }

    private function readMultilayerKey(array $topic, string $key): string
    {
        $keyList = explode('.', $key);
        $layer = $topic;
        foreach ($keyList as $layerKey) {
            if (! isset($layer[$layerKey])) {
                return '';
            }
            $layer = $layer[$layerKey];
        }

        return $layer;
    }

    /**
     * @throws GuzzleException
     */
    private function decodeJWT(string $jwt): ?array
    {
        [$header, $content, $signature] = explode('.', $jwt);

        $tokenData = json_decode($this->decodeBase64Url($content), true);

        if ($this->trimTrailingSlash($tokenData['iss']) != $this->providerUrl) {
            $this->displayError('oidc.error.providerMismatch', $tokenData['iss'], $this->providerUrl);
        }

        $headerData = json_decode($this->decodeBase64Url($header), true);

        if (! isset($headerData['kid'])) {
            throw new \RuntimeException('JWT token could not be decoded');
        }

        $key = $this->getPublicKey($headerData['kid']);

        if ($key === false) {
            return null;
        }

        $data = $header.'.'.$content;

        if (openssl_verify($data, $this->decodeBase64Url($signature), $key, $this->getAlgorythm($header)) === 1) {
            return $tokenData;
        }

        return null;
    }

    private function getAlgorythm(string $header): int
    {
        $algorythmName = json_decode($this->decodeBase64Url($header), true)['alg'];

        $map = [
            'RS256' => OPENSSL_ALGO_SHA256,
        ];

        if (! isset($map[$algorythmName])) {
            $this->displayError('oidc.error.unsupportedAlgorythm', $algorythmName);
        }

        return $map[$algorythmName];
    }

    /**
     * @throws GuzzleException
     */
    private function getPublicKey(string $kid): OpenSSLAsymmetricKey|false
    {
        if ($this->certificateString) {
            return openssl_pkey_get_public($this->certificateString);
        }
        if ($this->certificateFile) {
            return openssl_pkey_get_public(file_get_contents($this->certificateFile));
        }

        $httpClient = Http::withoutVerifying();
        // AUTH HEADER?
        $response = $httpClient->get($this->getJwksUrl()); // https://cloud.lukas-sieper.de/apps/oidc/jwks
        $keys = json_decode($response->getBody()->getContents(), true);
        if (isset($keys['keys'])) {
            $keys = $keys['keys'];
        }

        foreach ($keys as $possibleKid => $key) {
            $keySource = '';

            if (is_string($possibleKid)) {
                // old format like https://www.googleapis.com/oauth2/v1/certs
                if ($possibleKid == $kid) {
                    $keySource = $key;
                }
            } elseif (! isset($kid[0]) || $kid == $key['kid']) {
                $keySource = '';
                if (isset($key['x5c'])) {
                    $keySource = '-----BEGIN CERTIFICATE-----'.PHP_EOL.chunk_split($key['x5c'][0], 64, PHP_EOL).'-----END CERTIFICATE-----';
                } elseif (isset($key['n']) && isset($key['e'])) {
                    // Parse the public key from n and e
                    $modulus = $this->base64UrlDecode($key['n']);
                    $exponent = $this->base64UrlDecode($key['e']);
                    $keySource = $this->createPublicKey($modulus, exponent: $exponent);
                } else {
                    $this->displayError('oidc.error.unsupportedKeyFormat');
                }
            }

            if ($keySource) {
                return openssl_pkey_get_public($keySource);
            }
        }

        return false;
    }

    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }

    // key to PEM
    private function createPublicKey(string $modulus, string $exponent): string
    {

        $rsa = PublicKeyLoader::load([
            'e' => new BigInteger($exponent, 256),
            'n' => new BigInteger($modulus, 256),
        ]);

        return $rsa->__toString();

    }

    /**
     * @throws GuzzleException
     */
    private function getJwksUrl(): string
    {
        $this->loadEndpoints();

        return $this->jwksUrl;
    }

    /**
     * @throws GuzzleException
     */
    private function getTokenUrl(): string
    {
        if (! empty($this->tokenUrl || $this->loadEndpoints())) {
            return $this->tokenUrl;
        }

        return false;
    }

    /**
     * @throws GuzzleException
     */
    private function loadEndpoints(): bool
    {
        if ($this->configLoaded) {
            return true;
        }

        if ($this->authUrl && $this->tokenUrl && $this->jwksUrl) {
            $this->configLoaded = true;

            return true;
        }

        $httpClient = Http::withoutVerifying();
        try {
            // $uri = strlen() ? $this->autoDiscoverUrl : $this->providerUrl;
            $uri = empty($this->autoDiscoverUrl) ? $this->providerUrl : $this->autoDiscoverUrl;
            $response = $httpClient->get($uri.'/.well-known/openid-configuration');
            $endpoints = $response->collect()->toArray();
        } catch (\Exception $e) {

            Log::error('OIDC: '.$e->getMessage());
            Log::error($e);

            return false;
        }
        // load all not yet defined endpoints from well-known configuration

        if (! $this->authUrl || $this->authUrl === '') {
            $this->authUrl = $endpoints['authorization_endpoint'];
        }

        if (! $this->tokenUrl || $this->tokenUrl === '') {
            $this->tokenUrl = $endpoints['token_endpoint'];
        }

        if (! $this->jwksUrl || $this->jwksUrl === '') {
            $this->jwksUrl = $endpoints['jwks_uri'];
        }

        if (! $this->userInfoUrl || $this->userInfoUrl === '') {
            $this->userInfoUrl = $endpoints['userinfo_endpoint'];
        }

        return true;
    }

    /**
     * @throws GuzzleException
     */
    private function getMultiUrl(string $urls, string $token = ''): array
    {
        $urlList = explode(',', $urls);
        $httpClient = new Client;
        $combinedArray = [];

        $options = [];
        if ($token) {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ];
        }

        foreach ($urlList as $url) {
            $response = $httpClient->get($url, $options);
            $urlData = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            if (is_array($urlData)) {
                $combinedArray = array_merge_recursive($combinedArray, $urlData);
            }
        }

        return $combinedArray;
    }

    private function buildRedirectUrl(): string
    {
        return $this->trimTrailingSlash(BASE_URL).'/oidc/callback';

    }

    /**
     * @throws \Exception
     */
    private function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function verifyState(string $state): bool
    {
        // TODO
        return true;
    }

    private function encodeBase64Url(string $value): string
    {
        return strtr(base64_encode($value), '+/', '-_');
    }

    private function decodeBase64Url(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'));
    }

    /**
     * @throws HttpResponseException
     */
    private function displayError(string $translationKey, string ...$values): void
    {

        throw new \RuntimeException(sprintf($this->language->__($translationKey), ...$values));
    }
}
