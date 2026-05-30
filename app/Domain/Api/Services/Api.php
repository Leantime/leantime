<?php

namespace Leantime\Domain\Api\Services;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Api\Contracts\StaticAssetType;
use Leantime\Domain\Api\Repositories\Api as ApiRepository;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use RangeException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Api
{
    use DispatchesEvents;

    private ApiRepository $apiRepository;

    private UserRepository $userRepo;

    private ProjectRepository $projectRepo;

    private MenuRepository $menuRepo;

    private ?array $error = null;

    /**
     * @api
     */
    public function __construct(
        ApiRepository $apiRepository,
        UserRepository $userRepo,
        ProjectRepository $projectRepo,
        MenuRepository $menuRepo
    ) {
        $this->apiRepository = $apiRepository;
        $this->userRepo = $userRepo;
        $this->projectRepo = $projectRepo;
        $this->menuRepo = $menuRepo;
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getAPIKeyUser(string $apiKey): bool|array
    {

        // Split apiKey into parts
        $apiKeyParts = explode('_', $apiKey);

        if (! is_array($apiKeyParts) || count($apiKeyParts) != 3) {
            return false;
        }

        $namespace = $apiKeyParts[0];
        $user = $apiKeyParts[1];
        $key = $apiKeyParts[2];

        if ($namespace != 'lt') {
            return false;
        }

        $apiUser = $this->apiRepository->getAPIKeyUser($user);

        if ($apiUser) {
            if (password_verify($key, $apiUser['password'])) {

                $this->setApiUserSession($apiUser, true);

                return $apiUser;
            }
        }

        return false;
    }

    /**
     * @return void
     *
     * @throws BindingResolutionException
     *
     * Note: This is deliberately a duplicate of the authService setSession method to not have to load the authService
     * which will run db connections when we are not ready yet.
     * TODO: Move session management into a dedicated service
     */
    public function setApiUserSession(array $user, bool $isExternalAuth = false)
    {

        $currentUser = [
            'id' => (int) $user['id'],
            'name' => strip_tags($user['firstname']),
            'profileId' => $user['profileId'],
            'mail' => filter_var($user['username'], FILTER_SANITIZE_EMAIL),
            'clientId' => $user['clientId'],
            'role' => Roles::getRoleString($user['role']),
            'settings' => $user['settings'] ? safe_unserialize($user['settings'], []) : [],
            'twoFAEnabled' => $user['twoFAEnabled'] ?? false,
            'twoFAVerified' => false,
            'twoFASecret' => $user['twoFASecret'] ?? '',
            'isExternalAuth' => $isExternalAuth,
            'createdOn' => ! empty($user['createdOn']) ? dtHelper()->parseDbDateTime($user['createdOn']) : dtHelper()->userNow(),
            'modified' => ! empty($user['modified']) ? dtHelper()->parseDbDateTime($user['modified']) : dtHelper()->userNow(),
        ];

        $currentUser = self::dispatch_filter('user_session_vars', $currentUser);

        // Session handler for api is array
        session(['userdata' => $currentUser]);

    }

    /**
     * createAPIKey - simple service wrapper to create a new user
     *
     * TODO: Should accept userModel
     *
     * @param array $values basic user values

     * @return bool|array returns new user id on success, false on failure

     *
     * @throws Exception
     *
     * @api
     */
    public function createAPIKey(array $values): bool|array
    {
        $user = $this->randomStr(32);
        $password = $this->randomStr(32);

        $values['user'] = $user;
        $values['lastname'] = '';
        $values['passwordClean'] = $password;
        $values['password'] = $password;
        $values['status'] = 'a';
        $values['clientId'] = '';
        $values['phone'] = '';
        $values['id'] = $this->userRepo->addUser($values);

        return $values['id'] ? $values : false;
    }

    /**
     * Loads the stored values of an existing API key (user row) and maps them
     * into the value array used by the API key edit form.
     *
     * @param  int  $id  API key (user) id
     * @return array Mapped value array
     *
     * @throws Exception When the id is not a positive integer
     *
     * @api
     */
    public function getApiKeyFormValues(int $id): array
    {
        if ($id <= 0) {
            throw new Exception('Invalid API key id');
        }

        $row = $this->userRepo->getUser($id);

        return [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'status' => $row['status'],
            'role' => $row['role'],
            'hours' => $row['hours'],
            'wage' => $row['wage'],
            'clientId' => $row['clientId'],
            'source' => $row['source'],
            'pwReset' => $row['pwReset'],
        ];
    }

    /**
     * Updates an existing API key and reconciles its project relations.
     *
     * The save values are intentionally normalized the same way the legacy
     * controller did: only firstname/status/role are taken from the posted
     * values, everything else is blanked and the source stays 'api'.
     *
     * @param  int  $id  API key (user) id
     * @param  array  $postValues  Posted form values (firstname, status, role, ...)
     * @param  array|null  $projects  Selected project ids, or null when none submitted
     *
     * @throws Exception When the id is not a positive integer
     *
     * @api
     */
    public function updateApiKey(int $id, array $postValues, ?array $projects): bool
    {
        if ($id <= 0) {
            throw new Exception('Invalid API key id');
        }

        $row = $this->userRepo->getUser($id);

        $values = [
            'firstname' => ($postValues['firstname'] ?? $row['firstname']),
            'lastname' => '',
            'user' => $row['username'],
            'phone' => '',
            'status' => ($postValues['status'] ?? $row['status']),
            'role' => ($postValues['role'] ?? $row['role']),
            'hours' => '',
            'wage' => '',
            'clientId' => '',
            'password' => '',
            'source' => 'api',
            'pwReset' => '',
        ];

        $this->userRepo->editUser($values, $id);

        $this->reconcileProjectRelations($id, $projects);

        return true;
    }

    /**
     * Creates a new API key and reconciles its project relations.
     *
     * @param  array  $values  Basic user/key values (firstname, role, ...)
     * @param  array|null  $projects  Selected project ids, or null when none submitted
     * @return array|false The created key values on success, false on failure
     *
     * @throws Exception
     *
     * @api
     */
    public function createApiKeyWithProjects(array $values, ?array $projects): array|false
    {
        $apiKeyValues = $this->createAPIKey($values);

        if ($apiKeyValues === false) {
            return false;
        }

        if (is_array($projects) && count($projects) > 0) {
            $this->reconcileProjectRelations((int) $apiKeyValues['id'], $projects);
        }

        return $apiKeyValues;
    }

    /**
     * Reconciles the project relations for an API key (user).
     *
     * Mirrors the legacy controller behaviour: a leading "0" selection (or no
     * selection at all) clears all relations, otherwise the relations are set.
     *
     * @param  int  $id  API key (user) id
     * @param  array|null  $projects  Selected project ids
     */
    private function reconcileProjectRelations(int $id, ?array $projects): void
    {
        if (is_array($projects) && isset($projects[0]) && $projects[0] !== '0') {
            $this->projectRepo->editUserProjectRelations($id, $projects);

            return;
        }

        $this->projectRepo->deleteAllProjectRelations($id);
    }

    /**
     * Returns the list of project ids an API key (user) is related to.
     *
     * @param  int  $id  API key (user) id
     * @return array List of project ids
     *
     * @api
     */
    public function getProjectRelationIds(int $id): array
    {
        $projects = $this->projectRepo->getUserProjectRelation($id);

        $relations = [];
        foreach ($projects as $projectId) {
            $relations[] = $projectId['projectId'];
        }

        return $relations;
    }

    /**
     * Returns all projects (for populating the API key form selectors).
     *
     * @api
     */
    public function getAllProjects(): array
    {
        return $this->projectRepo->getAll();
    }

    /**
     * Returns the list of valid API key (user) status values.
     *
     * @api
     */
    public function getUserStatusOptions(): array
    {
        return $this->userRepo->status;
    }

    /**
     * Generates a new form (CSRF) token and stores it in the session so the
     * API key form can validate the subsequent submission.
     *
     * @api
     */
    public function generateFormToken(): void
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);
    }

    /**
     * getAPIKeys - gets api keys (users) from user table
     *
     *
     *
     * @api
     */
    public function getAPIKeys(): false|array
    {
        $keys = $this->userRepo->getAllBySource('api');

        foreach ($keys as &$key) {
            $key['username'] = substr($key['username'], 0, 5);
        }

        return $keys;
    }

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * This function uses type hints now (PHP 7+ only), but it was originally
     * written for PHP 5 as well.
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param  int  $length  How many characters do we want?
     * @param  string  $keyspace  A string of all possible characters to select from
     *
     * @throws Exception
     *
     * @api
     */
    public function randomStr(
        int $length = 64,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new RangeException('Length must be a positive integer');
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    /**
     * @todo Remove this.
     *
     * @see ../Controllers/Tickets.php
     *
     * @api
     */
    public function jsonResponse(int $id, ?array $result): void
    {
        $jsonRPCArray = [
            'jsonrpc' => '2.0',
        ];

        header('Content-Type: application/json; charset=utf-8');

        if ($this->error != null) {
            $jsonRPCArray['error'] = $this->error;
        } elseif ($result !== null) {
            $jsonRPCArray['result'] = $result;
        }

        echo json_encode($jsonRPCArray);
    }

    /**
     * Check the manifest for the asset and serve if found.
     *
     * @api
     */
    public function getCaseCorrectPathFromManifest(string $filepath): string|false
    {
        $manifest = mix('')->getManifest();
        $clone = array_change_key_case(collect(Arr::dot($manifest))
            ->mapWithKeys(fn ($value, $key) => [Str::of($key)->replaceFirst('./', '/')->lower()->toString() => $value])
            ->all());

        if (is_null($referenceValue = $clone[strtolower($filepath)] ?? null)) {
            return false;
        }

        $correctManifest = array_filter($manifest, fn ($arr) => in_array($referenceValue, $arr));
        $basePath = array_keys($correctManifest)[0];
        $correctManifest = array_values($correctManifest)[0];

        return $basePath.array_search($referenceValue, $correctManifest);
    }

    /**
     * Resolves a static asset request path into an on-disk path and its
     * asset type.
     *
     * Maps the request URI to the filesystem app path, validates the extension
     * against the StaticAssetType enum, rewrites phar paths, and resolves the
     * case-correct path via the mix manifest.
     *
     * @param  string  $pathInfo  The request path info (e.g. /api/static-asset/...)
     * @param  bool  $debug  Whether debug mode is enabled (affects failure behaviour)
     * @return array{path: string, type: StaticAssetType}|false The resolved asset, or false on failure
     *
     * @throws BadRequestHttpException When the extension is not a known asset type and debug is on
     * @throws NotFoundHttpException When the asset is not found in the manifest and debug is on
     *
     * @api
     */
    public function resolveStaticAsset(string $pathInfo, bool $debug = false): array|false
    {
        $fullpath = Str::of($pathInfo)
            ->replaceFirst('/api/static-asset/', APP_ROOT.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR)
            ->replace('/', DIRECTORY_SEPARATOR)
            ->lower();

        // Check if it's a static asset
        if (! defined($constant = StaticAssetType::class.'::'.$fullpath->afterLast('.')->upper())) {
            if ($debug) {
                throw new BadRequestHttpException;
            }

            return false;
        }

        if (Str::contains($fullpath, '.phar') && ! Str::startsWith($fullpath, 'phar://')) {
            $fullpath = 'phar://'.$fullpath;
        }

        /** @var StaticAssetType $type */
        $type = constant($constant);

        if (! $correctPath = $this->getCaseCorrectPathFromManifest((string) $fullpath)) {
            if ($debug) {
                throw new NotFoundHttpException;
            }

            return false;
        }

        return [
            'path' => $correctPath,
            'type' => $type,
        ];
    }

    /**
     * Persists the collapsed/expanded state of a submenu.
     *
     * @param  string  $submenu  Submenu identifier
     * @param  string  $state  Submenu state
     *
     * @api
     */
    public function setSubmenuState(string $submenu, string $state): void
    {
        $this->menuRepo->setSubmenuState($submenu, $state);
    }

    /**
     * Persists the main menu state both in the session and the menu store.
     *
     * @param  string  $state  Raw main menu state from the request
     *
     * @api
     */
    public function setMainMenuState(string $state): void
    {
        session(['menuState' => htmlentities($state)]);
        $this->menuRepo->setSubmenuState('mainMenu', $state);
    }

    /**
     * Persists whether the product tour is active in the session.
     *
     * @param  mixed  $tourActive  Raw tour flag from the request
     *
     * @api
     */
    public function setTourActive($tourActive): void
    {
        session(['tourActive' => filter_var($tourActive, FILTER_SANITIZE_NUMBER_INT)]);
    }

    /**
     * @return true
     */
    public function healthCheck()
    {
        return true;
    }
}
