<?php

namespace Leantime\Domain\Oidc\Services;

//This class Handles authentication via OpenID Connect (OIDC)

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use OpenSSLAsymmetricKey;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Oidc
{
    private Environment $config;
    private Language $Language;
    private AuthService $authService;
    public UserRepository $userRepo;

    private bool $configLoaded = false;

    private string $providerUrl = '';
    private string $clientId = '';
    private string $clientSecret = '';
    private string $authUrl = '';
    private string $tokenUrl = '';
    private string $jwksUrl = '';
    private string $userInfoUrl = '';
    private string $certificateString = '';
    private string $certificateFile = '';
    private string $scopes = '';

    private bool $createUser = false;

    private int $defaultRole = 20; //20 == editor
    private string $fieldEmail = '';
    private string $fieldFirstName = '';
    private string $fieldLastName = '';

    private string $fieldPhone = '';

    private string $fieldJobtitle = '';

    private string $fieldJoblevel = '';

    private string $fieldDepartment = '';
    private Language $language;

    /**
     * @param Environment    $config
     * @param Language       $language
     * @param AuthService    $authService
     * @param UserRepository $userRepo
     */
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

        $this->providerUrl = !empty($providerUrl) ? $this->trimTrailingSlash($providerUrl) : $providerUrl;
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

    /**
     * @param string $str
     * @return string
     */
    private function trimTrailingSlash(string $str): string
    {
        $almost = strlen($str) - 1;
        if ($str[$almost] == '/') {
            return substr($str, 0, $almost);
        }
        return $str;
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    public function buildLoginUrl(): string
    {
        return $this->getAuthUrl() . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->buildRedirectUrl(),
            'response_type' => 'code',
            'scope' => $this->scopes,
            'state' => $this->generateState(),
        ]);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    private function getAuthUrl(): string
    {
        $this->loadEndpoints();
        return $this->authUrl;
    }

    /**
     * @param string $code
     * @param string $state
     * @return void
     * @throws GuzzleException
     */
    public function callback(string $code, string $state): Response
    {
        if (!$this->verifyState($state)) {
            $this->displayError('oidc.error.invalidState');
        }

        $tokens = $this->requestTokens($code);

        $userInfo = null;
        //echo '<pre>' . print_r($tokens, true) . '</pre>';
        if (isset($tokens['id_token'])) {
            $userInfo = $this->decodeJWT($tokens['id_token']);
        } elseif (isset($tokens['access_token'])) {
            //falback to OAuth userinfo endpoint
            $userInfo = $this->pollUserInfo($tokens['access_token']);
        } else {
            $this->displayError("oidc.error.unsupportedToken");
        }

        if ($userInfo == null) {
            //TODO: invalid token
            $this->displayError('oidc.error.invalidToken');
        }

        return $this->login($userInfo);
    }

    /**
     * @param string $token
     * @return array
     * @throws GuzzleException
     */
    private function pollUserInfo(string $token): array
    {
        return $this->getMultiUrl($this->userInfoUrl, $token);
    }

    /**
     * @param array $userInfo
     * @return void
     */
    private function login(array $userInfo): Response
    {

        $userName = $this->readMultilayerKey($userInfo, $this->fieldEmail);

        if (!$userName) {
            $this->displayError('oidc.error.emailUnavailable');
        }

        $user = $this->userRepo->getUserByEmail($userName);

        if ($user === false) {
            if ($this->createUser) {
                //create user if it doesn't exist yet
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
                    throw new \Exception("OIDC user creation failed.");
                }
            } else {
                $this->displayError('oidc.error.user_not_found');
            }
        } else {
            //update user if it exists
            $user['user'] = $user['username'];
            $user['firstname'] = $this->readMultilayerKey($userInfo, $this->fieldFirstName) != "" ? $this->readMultilayerKey($userInfo, $this->fieldFirstName) : $user['firstname'];
            $user['lastname'] = $this->readMultilayerKey($userInfo, $this->fieldLastName) != "" ? $this->readMultilayerKey($userInfo, $this->fieldLastName) : $user['lastname'];
            $user['phone'] = $this->readMultilayerKey($userInfo, $this->fieldPhone)  != "" ?  $this->readMultilayerKey($userInfo, $this->fieldPhone) : $user['phone'];
            $user['jobTitle'] = $this->readMultilayerKey($userInfo, $this->fieldJobtitle) != "" ? $this->readMultilayerKey($userInfo, $this->fieldJobtitle) : $user['jobTitle'];
            $user['jobLevel'] = $this->readMultilayerKey($userInfo, $this->fieldJoblevel) != "" ?  $this->readMultilayerKey($userInfo, $this->fieldJoblevel) : $user['jobLevel'];
            $user['department'] = $this->readMultilayerKey($userInfo, $this->fieldDepartment) != "" ? $this->readMultilayerKey($userInfo, $this->fieldDepartment) : $user['department'];

            $user['role'] = $this->getUserRole($userInfo, $user);

            $this->userRepo->editUser($user, $user['id']);

            //Get updated user
            $user = $this->userRepo->getUserByEmail($userName);
        }

        $this->authService->setUserSession($user, false);

        return Frontcontroller::redirect(BASE_URL . "/dashboard/home");
    }

    /**
     * @param array $userInfo
     * @param array $user
     * @return string
     */
    private function getUserRole(array $userInfo, array $user = []): string
    {
        return $user['role'] ?? 'readonly';
    }

    /**
     * @param string $code
     * @return array
     * @throws GuzzleException
     */
    private function requestTokens(string $code): array
    {
        $httpClient = new Client();
        $response = $httpClient->post($this->getTokenUrl(), [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->buildRedirectUrl(),
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);
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

    /**
     * @param array  $topic
     * @param string $key
     * @return string
     */
    private function readMultilayerKey(array $topic, string $key): string
    {
        $keyList = explode('.', $key);
        $layer = $topic;
        foreach ($keyList as $layerKey) {
            if (!isset($layer[$layerKey])) {
                return '';
            }
            $layer = $layer[$layerKey];
        }
        return $layer;
    }

    /**
     * @param string $jwt
     * @return array|null
     * @throws GuzzleException
     */
    private function decodeJWT(string $jwt): array|null
    {
        list($header, $content, $signature) = explode('.', $jwt);

        $tokenData = json_decode($this->decodeBase64Url($content), true);


        if ($this->trimTrailingSlash($tokenData['iss']) != $this->providerUrl) {
            $this->displayError('oidc.error.providerMismatch', $tokenData['iss'], $this->providerUrl);
        }

        $headerData = json_decode($this->decodeBase64Url($header), true);


        $key = $this->getPublicKey($headerData['kid']);

        if ($key === false) {
            return null;
        }

        $data = $header . '.' . $content;

        if (openssl_verify($data, $this->decodeBase64Url($signature), $key, $this->getAlgorythm($header)) === 1) {
            return $tokenData;
        }

        return null;
    }

    /**
     * @param string $header
     * @return int
     */
    private function getAlgorythm(string $header): int
    {
        $algorythmName = json_decode($this->decodeBase64Url($header), true)['alg'];

        $map = [
            'RS256' => OPENSSL_ALGO_SHA256,
        ];

        if (!isset($map[$algorythmName])) {
            $this->displayError("oidc.error.unsupportedAlgorythm", $algorythmName);
        }


        return $map[$algorythmName];
    }

    /**
     * @param string $kid
     * @return OpenSSLAsymmetricKey|false
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



        $httpClient = new Client();
        $response = $httpClient->get($this->getJwksUrl());
        $keys = json_decode($response->getBody()->getContents(), true);
        if (isset($keys['keys'])) {
            $keys = $keys['keys'];
        }

        foreach ($keys as $possibleKid => $key) {
            $keySource = '';

            if (is_string($possibleKid)) {
                //old format like https://www.googleapis.com/oauth2/v1/certs
                if ($possibleKid == $kid) {
                    $keySource = $key;
                }
            } elseif (!isset($kid[0]) || $kid == $key['kid']) {
                $keySource = '';
                if (isset($key['x5c'])) {
                    $keySource = '-----BEGIN CERTIFICATE-----' . PHP_EOL . chunk_split($key['x5c'][0], 64, PHP_EOL) . '-----END CERTIFICATE-----';
                } elseif (isset($key['n'])) {
                    $this->displayError('oidc.error.unsupportedKeyFormat');
                }
            }

            if ($keySource) {
                return openssl_pkey_get_public($keySource);
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    private function getJwksUrl(): string
    {
        $this->loadEndpoints();
        return $this->jwksUrl;
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    private function getTokenUrl(): string
    {
        $this->loadEndpoints();
        return $this->tokenUrl;
    }

    /**
     * @return bool
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

        $httpClient = new Client();
        try {
            $response = $httpClient->get($this->providerUrl . '/.well-known/openid-configuration');
            $endpoints = json_decode($response->getBody()->getContents(), true);
        }catch(\Exception $e) {
            report($e);
            return false;
        }
        //load all not yet defined endpoints from well-known configuration

        if (!$this->authUrl || $this->authUrl == '') {
            $this->authUrl = $endpoints['authorization_endpoint'];
        }

        if (!$this->tokenUrl || $this->tokenUrl == '') {
            $this->tokenUrl = $endpoints['token_endpoint'];
        }

        if (!$this->jwksUrl || $this->jwksUrl == '') {
            $this->jwksUrl = $endpoints['jwks_uri'];
        }

        if (!$this->userInfoUrl || $this->userInfoUrl == '') {
            $this->userInfoUrl = $endpoints['userinfo_endpoint'];
        }

        return true;
    }

    /**
     * @param string $urls
     * @param string $token
     * @return array
     * @throws GuzzleException
     */
    private function getMultiUrl(string $urls, string $token = ''): array
    {
        $urlList = explode(',', $urls);
        $httpClient = new Client();
        $combinedArray = [];

        $options = [];
        if ($token) {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ];
        }

        foreach ($urlList as $url) {
            $response = $httpClient->get($url, $options);
            $urlData = json_decode($response->getBody()->getContents(), true);
            if (is_array($urlData)) {
                $combinedArray = array_merge_recursive($combinedArray, $urlData);
            }
        }
        return $combinedArray;
    }

    /**
     * @return string
     */
    private function buildRedirectUrl(): string
    {
        return $this->trimTrailingSlash(BASE_URL) . '/oidc/callback';
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param string $state
     * @return bool
     */
    private function verifyState(string $state): bool
    {
        //TODO
        return true;
    }

    /**
     * @param string $value
     * @return string
     */
    private function encodeBase64Url(string $value): string
    {
        return strtr(base64_encode($value), '+/', '-_');
    }

    /**
     * @param string $value
     * @return string
     */
    private function decodeBase64Url(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'));
    }

    /**
     * @param string $translationKey
     * @param string ...$values
     * @throws HttpResponseException
     * @return void
     */
    private function displayError(string $translationKey, string ...$values): void
    {

        throw new \Exception(sprintf($this->language->__($translationKey), ...$values));
    }
}
