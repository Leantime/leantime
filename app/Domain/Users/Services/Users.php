<?php

namespace Leantime\Domain\Users\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Ldap\Services\Ldap as LdapService;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Ramsey\Uuid\Uuid;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
class Users
{
    use DispatchesEvents;

    public function __construct(
        protected UserRepository $userRepo,
        protected LanguageCore $language,
        protected ProjectRepository $projectRepository,
        protected ClientRepository $clientRepo,
        protected AuthService $authService,
        protected Files $fileService,
        protected Avatarcreator $avatarcreator,
        protected SettingService $settingsService,
        protected ThemeCore $themeCore,
        protected ProjectService $projectService
    ) {}

    // GET

    /**
     * @return SVG|Response|string Returns either an SVG file, a file response or a path to a file
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getProfilePicture($id): SVG|Response|string
    {

        // Get profile picture definition from db
        $profile = $this->userRepo->getProfilePicture($id);

        // If can't find user, return ghost
        if (empty($profile)) {
            return $this->avatarcreator->getAvatar('👻');
        }

        // If user uploaded return uploaded file
        if (! empty($profile['profileId'])) {

            $file = $this->fileService->getFileById($profile['profileId']);
            if ($file) {
                return $file;
            }

        }

        // Otherwise return avatar
        $name = $profile['firstname'].' '.$profile['lastname'];

        return $this->avatarcreator->getAvatar($name);

    }

    /**
     * @api
     */
    public function editUser($values, $id): bool
    {

        $results = $this->userRepo->editUser($values, $id);
        self::dispatch_event('editUser', ['id' => $id, 'values' => $values]);

        return $results;
    }

    /**
     * @api
     */
    public function getNumberOfUsers(bool $activeOnly = false, bool $includeApi = true): int
    {
        $filters = [];

        if ($activeOnly) {
            $filters[] = ['status', '=', 'a'];
        }

        if (! $includeApi) {
            $filters[] = ['source', '!=', 'api'];
        }

        return $this->userRepo->getNumberOfUsers($filters);
    }

    /**
     * @param  false  $activeOnly
     *
     * @api
     */
    public function getAll(bool $activeOnly = false): mixed
    {
        $users = $this->userRepo->getAll($activeOnly);

        $users = self::dispatch_filter('getAll', $users);

        return $users;
    }

    /**
     * Request-level cache for getUser() results.
     * Prevents duplicate DB queries when the same user is fetched
     * multiple times within a single request (common in view composers).
     *
     * @var array<int|string, array|bool>
     */
    private array $userCache = [];

    /**
     * @api
     *
     * Default to the session user when no id is provided. Same server-
     * authoritative pattern as getUsersWithProjectAccess — mobile clients
     * authenticate via Bearer token and don't always know their own real
     * user id (the login response returns the access-token row id, not
     * the underlying user id), so they need a "who am I" endpoint that
     * doesn't require the answer they're trying to look up.
     */
    public function getUser($id = null): array|bool
    {
        $resolvedId = $id;
        if ($resolvedId === null || (int) $resolvedId === 0) {
            $resolvedId = (int) session('userdata.id');
            if ($resolvedId === 0) {
                return false;
            }
        }

        if (isset($this->userCache[$resolvedId])) {
            return $this->userCache[$resolvedId];
        }

        $user = $this->userRepo->getUser($resolvedId);
        $this->userCache[$resolvedId] = $user;

        return $user;
    }

    /**
     * @api
     */
    public function getUserByEmail($email, $status = 'a'): false|array
    {
        return $this->userRepo->getUserByEmail($email, $status);
    }

    /**
     * @api
     */
    public function getAllBySource($source): false|array
    {
        return $this->userRepo->getAllBySource($source);
    }

    // POST

    /**
     * @throws BindingResolutionException
     *
     * @internal Not @api: invoked only by Users\Controllers\ProfileImage, which always
     *           passes the session user's id. The method trusts the $id it is given, so
     *           exposing it over JSON-RPC would let a user overwrite another user's photo.
     */
    public function setProfilePicture($photo, $id): void
    {
        $user = $this->getUser($id);

        // Save the path to the old picture
        if (isset($user['profileId']) && $user['profileId'] > 0) {
            $oldPicture = $user['profileId'];
        }

        $leantimeFile = $this->fileService->upload($photo, 'user', $id);

        if ($leantimeFile
            && $this->userRepo->setPicture($leantimeFile['fileId'], $id)
            && $oldPicture) {

            try {
                $this->fileService->deleteFile($oldPicture);
            } catch (\Exception $e) {
                Log::warning('Could not delete old profile picture: '.$e->getMessage());
                Log::warning($e);
            }

        }

    }

    /**
     * @api
     */
    public function updateUserSettings($category, $setting, $value): bool
    {

        $filteredInput = htmlspecialchars($setting);
        $filteredValue = htmlspecialchars($value);

        session(['usersettings.'.$category.'.'.$filteredInput => $filteredValue]);

        $serializeSettings = serialize(session('usersettings'));

        return $this->userRepo->patchUser(session('userdata.id'), ['settings' => $serializeSettings]);
    }

    /**
     * Records that the current user dismissed a modal.
     *
     * The dismissal is always stored in the session. When $permanent is true it is
     * additionally persisted to the user's stored settings so the modal stays
     * dismissed across sessions.
     *
     * @param  string  $modalKey  The modal identifier being dismissed
     * @param  bool  $permanent  Whether to persist the dismissal beyond the current session
     * @return bool True on success
     *
     * @api
     */
    public function saveModalDismissal(string $modalKey, bool $permanent): bool
    {
        if ($permanent) {
            // updateUserSettings sanitizes the key, records it in the session, and persists it.
            return $this->updateUserSettings('modals', $modalKey, 1);
        }

        $key = htmlspecialchars($modalKey);

        if (! session()->exists('usersettings.modals')) {
            session(['usersettings.modals' => []]);
        }

        session(['usersettings.modals.'.$key => 1]);

        return true;
    }

    /**
     * checkPasswordStrength - Checks password strength for minimum requirements
     * Current requirements are:
     * Password must be at least 8 characters in length.
     * Password must include at least one upper case letter.
     * Password must include at least one number.
     * Password must include at least one special character.
     *
     * @param  string  $password  The string to be checked
     * @return bool returns true if password meets requirements
     *
     * @api
     */
    public function checkPasswordStrength(string $password): bool
    {

        // Validate password strength
        // Password must be at least 8 characters in length.
        // Password must include at least one upper case letter.
        // Password must include at least one number.
        // Password must include at least one special character.

        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if (! $uppercase || ! $lowercase || ! $number || ! $specialChars || strlen($password) < 8) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * createUserInvite - generates a new invite token, creates the user in the db and sends the invitation email TODO: Should accept userModel
     *
     * @param  array  $values  basic user values
     * @return bool|int returns new user id on success, false on failure
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function createUserInvite(array $values): bool|int
    {

        // Generate strong password
        $tempPasswordVar = Uuid::uuid4()->toString();
        $inviteCode = Uuid::uuid4()->toString();

        $values['password'] = $tempPasswordVar;
        $values['status'] = 'i';
        $values['pwReset'] = $inviteCode;

        $result = $this->userRepo->addUser($values);

        if ($result === false) {
            return false;
        }

        $this->sendUserInvite($inviteCode, $values['user']);

        return $result;
    }

    public function sendUserInvite(string $inviteCode, string $user)
    {

        $mailer = app()->make(MailerCore::class);
        $mailer->setContext('new_user');

        $mailer->setSubject($this->language->__('email_notifications.new_user_subject'));
        $actual_link = BASE_URL.'/auth/userInvite/'.$inviteCode;

        $message = sprintf(
            $this->language->__('email_notifications.user_invite_message'),
            session('userdata.name') ?? 'Leantime',
            $actual_link,
            $user
        );

        $mailer->setHtml($message);

        $to = [$user];

        $mailer->sendMail($to, session('userdata.name') ?? 'Leantime');
    }

    /**
     * addUser - simple service wrapper to create a new user
     *
     * TODO: Should accept userModel
     *
     * @param  array  $values  basic user values
     * @return bool|int returns new user id on success, false on failure
     *
     * @api
     */
    public function addUser(array $values): bool|int
    {
        $values = [
            'firstname' => $values['firstname'] ?? '',
            'lastname' => $values['lastname'] ?? '',
            'phone' => $values['phone'] ?? '',
            'user' => $values['username'] ?? $values['user'],
            'role' => $values['role'],
            'notifications' => $values['notifications'] ?? 1,
            'clientId' => $values['clientId'] ?? '',
            'password' => $values['password'],
            'source' => $values['source'] ?? '',
            'pwReset' => $values['pwReset'] ?? '',
            'status' => $values['status'] ?? '',
            'createdOn' => $values['createdOn'] ?? '',
            'jobTitle' => $values['jobTitle'] ?? '',
            'jobLevel' => $values['jobLevel'] ?? '',
            'department' => $values['department'] ?? '',
        ];

        return $this->userRepo->addUser($values);
    }

    /**
     * usernameExist - Checks if a given username (email) is already in the db
     *
     * TODO: Should accept userModel
     *
     * @param  string  $username  username
     * @param  int|string  $notUserId  optional userId to skip. (used when changing email addresses to a new one, skips checking the old one)
     * @return bool returns true or false
     *
     * @api
     */
    public function usernameExist(string $username, int|string $notUserId = ''): bool
    {
        return $this->userRepo->usernameExist($username, $notUserId);
    }

    /**
     * Patch specific fields on a user record.
     *
     * Only admins/owners can patch other users. Regular users may only patch
     * their own record and only non-privileged fields.
     *
     * @param  int  $id  The user ID
     * @param  array  $params  The fields to update
     *
     * @api
     */
    public function patchUser(int $id, array $params): bool
    {
        $currentUserId = (int) session('userdata.id');

        // Non-privileged fields that any user can update on their own profile
        $selfPatchableFields = [
            'firstname', 'lastname', 'phone', 'jobTitle', 'jobLevel',
            'department', 'password', 'notifications',
        ];

        // Privileged fields that only admins/owners can set (on any user)
        $adminPatchableFields = [
            'firstname', 'lastname', 'phone', 'jobTitle', 'jobLevel',
            'department', 'password', 'notifications', 'role', 'clientId',
            'status', 'user',
        ];

        if (Auth::userIsAtLeast(Roles::$admin)) {
            // Admins can patch any user, but only whitelisted fields
            $filteredParams = array_intersect_key($params, array_flip($adminPatchableFields));
        } elseif ($id === $currentUserId) {
            // Regular users can only patch their own profile with limited fields
            $filteredParams = array_intersect_key($params, array_flip($selfPatchableFields));
        } else {
            // Non-admin trying to patch another user
            return false;
        }

        if (empty($filteredParams)) {
            return false;
        }

        return $this->userRepo->patchUser($id, $filteredParams);
    }

    /**
     * Returns the list of available user statuses.
     *
     * @return array<string, string>
     */
    public function getUserStatuses(): array
    {
        return $this->userRepo->status;
    }

    /**
     * getUsersWithProjectAccess - gets all users who can access a project
     *
     * The $currentUser parameter is preserved for backwards compatibility
     * with existing positional callers (e.g. plugins, the original
     * Api/Controllers/Users.php call site). Default is null so RPC clients
     * (mobile) can call by name with only $projectId and have the session
     * user resolved server-side.
     *
     * Role-based authorization on $currentUser, per @marcelfolaron review:
     *   - Not passed (null/0): use the authenticated session user
     *   - Passed AND caller is admin/owner: honor the requested user id
     *   - Passed by a non-admin AND differs from session: ignored and
     *     overridden to the session user (prevents IDOR via the optional
     *     param)
     *
     * Param ORDER preserved from the original signature (currentUser, projectId)
     * so any positional callers don't have to be touched.
     *
     * TODO: Should return usermodel
     *
     * @param  int|null  $currentUser  Optional. If omitted or 0, resolves to
     *                                 the authenticated session user.
     * @param  int  $projectId  project id
     * @return array returns array of users
     *
     * @throws BindingResolutionException
     *
     * @internal Not exposed via JSON-RPC (projectId is unscoped). The JSON-RPC
     *           entry point is searchProjectUsers(), which scopes the result to
     *           a project the caller can actually access.
     */
    public function getUsersWithProjectAccess(?int $currentUser = null, int $projectId = 0): array
    {
        // projectId is logically required — defaulting to 0 only so the
        // optional $currentUser can sit ahead of it in the signature (PHP
        // requires defaults at the tail). Reject the missing-projectId
        // case explicitly here.
        if ($projectId === 0) {
            return [];
        }

        $sessionUser = (int) session('userdata.id');

        if ($currentUser === null || $currentUser === 0) {
            $currentUser = $sessionUser;
        } elseif ($currentUser !== $sessionUser && ! Auth::userIsAtLeast(Roles::$admin)) {
            $currentUser = $sessionUser;
        }

        if ($currentUser === 0) {
            return [];
        }

        $users = [];

        if ($this->projectRepository->isUserAssignedToProject($currentUser, $projectId)) {
            $project = $this->projectRepository->getProject($projectId);

            if ($project['psettings'] == 'all') {
                return $this->getAll();
            }

            if ($project['psettings'] == 'clients') {
                $clientUsers = $this->clientRepo->getClientsUsers($project['clientId']);
                $projectUsers = $this->projectRepository->getUsersAssignedToProject($projectId);
                $users = $clientUsers;

                foreach ($projectUsers as $user) {
                    $column = array_column($users, 'id');
                    $search = array_search($user['id'], $column);
                    if (array_search($user['id'], $column) === false) {
                        $users[] = $user;
                    }
                }

                return $users;
            }

            if ($project['psettings'] == 'restricted' || $project['psettings'] == '') {
                $users = $this->projectRepository->getUsersAssignedToProject($projectId);

                return $users;
            }
        }

        return [];
    }

    /**
     * Authorized JSON-RPC entry point: list users with access to a project,
     * optionally filtered by a search query (used by the @mention autocomplete).
     *
     * Resolves to the current project when no id is given. Project access is
     * enforced by getUsersWithProjectAccess() (which returns [] when the session
     * user is not assigned to the project), so a caller cannot enumerate the
     * users of a project they aren't part of.
     *
     * @param  int|null  $projectId  Project id, or null for the current project
     * @param  string  $query  Optional case-insensitive substring filter
     * @return array<int, array<string, mixed>> Matching users (empty when no access)
     *
     * @api
     */
    public function searchProjectUsers(?int $projectId = null, string $query = ''): array
    {
        $projectId = $projectId ?? (int) session('currentProject');

        $users = $this->getUsersWithProjectAccess(projectId: $projectId);

        if ($query === '') {
            return $users;
        }

        return array_values(
            array_filter($users, static fn (array $user) => stripos(implode(' ', $user), $query) !== false)
        );
    }

    /**
     * @api
     */
    public function editOwn($values, $id): void
    {
        $this->userRepo->editOwn($values, $id);

        $user = $this->getUser($id);

        $this->authService->setUserSession($user);

        self::dispatch_event('editUser', ['id' => $id, 'values' => $values]);
    }

    /**
     * Delete the user with the specified id.
     *
     * @param  int  $id  The id of the user to delete.
     * @return bool True if the user was deleted successfully, false otherwise.
     *
     * @throws \Exception If the user is not authorized to delete the user.
     *
     * @api
     */
    public function deleteUser(int $id): bool
    {

        if (Auth::userIsAtLeast(Roles::$admin, true)) {
            $this->userRepo->deleteUser($id);
            $this->projectRepository->deleteAllProjectRelations($id);

            return true;
        }

        throw new \Exception('Not authorized');
    }

    /**
     * Returns the list of users visible to the current session user.
     *
     * Admins/owners see every user. Anyone else is scoped to the users that
     * belong to their own client. This encapsulates the role-based data
     * scoping that previously lived in the controller.
     *
     * @return array<int, array<string, mixed>> Array of user rows.
     *
     * @api
     */
    public function getAllVisibleToUser(): array
    {
        if (Auth::userIsAtLeast(Roles::$admin)) {
            return $this->getAll();
        }

        return $this->userRepo->getAllClientUsers(Auth::getUserClientId());
    }

    /**
     * Gathers the profile + appearance/locale settings for the "edit own
     * profile" screen, applying all of the company/default fallbacks that
     * previously lived inline in the controller.
     *
     * @param  int  $userId  The id of the user whose profile is being viewed.
     * @return array<string, mixed> Template-ready settings keyed by view variable.
     *
     * @throws \Exception When the user cannot be found.
     *
     * @api
     */
    public function getOwnProfileSettings(int $userId): array
    {
        $row = $this->getUser($userId);

        if ($row === false) {
            throw new \Exception('User not found');
        }

        $userLang = $this->settingsService->getSetting('usersettings.'.$userId.'.language');
        if (! $userLang) {
            $userLang = $this->language->getCurrentLanguage();
        }

        $userTheme = $this->settingsService->getSetting('usersettings.'.$userId.'.theme');
        if (! $userTheme) {
            $userTheme = 'default';
        }

        $userColorMode = $this->settingsService->getSetting('usersettings.'.$userId.'.colorMode');
        if (! $userColorMode) {
            $userColorMode = 'light';
        }

        $availableColorSchemes = $this->themeCore->getAvailableColorSchemes();
        $userColorScheme = $this->settingsService->getSetting('usersettings.'.$userId.'.colorScheme');
        if (! $userColorScheme) {
            $userColorScheme = isset($availableColorSchemes['companyColors']) ? 'companyColors' : 'themeDefault';
        }

        $themeFont = $this->settingsService->getSetting('usersettings.'.$userId.'.themeFont');
        if (! $themeFont) {
            $themeFont = 'Roboto';
        }

        $userDateFormat = $this->settingsService->getSetting('usersettings.'.$userId.'.date_format');
        $userTimeFormat = $this->settingsService->getSetting('usersettings.'.$userId.'.time_format');

        $timezone = $this->settingsService->getSetting('usersettings.'.$userId.'.timezone');
        if (! $timezone) {
            $timezone = date_default_timezone_get();
        }

        $messagesfrequency = $this->settingsService->getSetting('usersettings.'.$row['id'].'.messageFrequency');
        if (! $messagesfrequency) {
            $messagesfrequency = $this->settingsService->getSetting('companysettings.messageFrequency');
        }

        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'role' => $row['role'],
            'jobTitle' => $row['jobTitle'],
            'jobLevel' => $row['jobLevel'],
            'department' => $row['department'],
            'notifications' => $row['notifications'],
            'twoFAEnabled' => $row['twoFAEnabled'],
            'messagesfrequency' => $messagesfrequency,
        ];

        return [
            'user' => $row,
            'values' => $values,
            'profilePic' => $this->userRepo->getProfilePicture($userId),
            'userLang' => $userLang,
            'userTheme' => $userTheme,
            'themeFont' => $themeFont,
            'userColorMode' => $userColorMode,
            'userColorScheme' => $userColorScheme,
            'languageList' => $this->language->getLanguageList(),
            'dateFormat' => $userDateFormat,
            'timeFormat' => $userTimeFormat,
            'dateTimeValues' => $this->getSupportedDateTimeFormats(),
            'timezone' => $timezone,
            'availableColorSchemes' => $availableColorSchemes,
            'availableFonts' => $this->themeCore->getAvailableFonts(),
            'availableThemes' => $this->themeCore->getAll(),
            'timezoneOptions' => timezone_identifiers_list(),
        ];
    }

    /**
     * Gathers the notification preferences for the "edit own profile" screen,
     * applying company defaults and the per-project notification levels
     * (including the lazy migration from the legacy muted-projects format).
     *
     * @param  int  $userId  The id of the user whose preferences are loaded.
     * @return array<string, mixed> Template-ready notification preference data.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getNotificationPreferences(int $userId): array
    {
        $enabledEventTypes = $this->settingsService->getSetting('usersettings.'.$userId.'.notificationEventTypes');
        if (! $enabledEventTypes) {
            $enabledEventTypes = $this->settingsService->getSetting('companysettings.defaultNotificationEventTypes');
        }
        if ($enabledEventTypes) {
            $enabledEventTypes = json_decode($enabledEventTypes, true);
        }
        if (! is_array($enabledEventTypes)) {
            $enabledEventTypes = Notification::getCategoryKeys();
        }

        $projectNotificationLevels = $this->loadProjectNotificationLevels($userId);

        // Use the same access-aware project list as the navigation project dropdown
        // This respects direct assignment, org-wide (psettings='all'), and client-scoped access
        // but does NOT include the admin bypass that shows all projects unconditionally
        $projectData = $this->projectService->getProjectHierarchyAvailableToUser($userId);
        $userProjects = $projectData['allAvailableProjects'] ?? [];

        $companyDefaultRelevance = $this->settingsService->getSetting('companysettings.defaultNotificationRelevance');
        if (! $companyDefaultRelevance || ! Notification::isValidRelevanceLevel($companyDefaultRelevance)) {
            $companyDefaultRelevance = Notification::RELEVANCE_ALL;
        }

        return [
            'notificationCategories' => Notification::NOTIFICATION_CATEGORIES,
            'enabledEventTypes' => $enabledEventTypes,
            'projectNotificationLevels' => $projectNotificationLevels,
            'companyDefaultRelevance' => $companyDefaultRelevance,
            'relevanceLevels' => Notification::RELEVANCE_LEVELS,
            'userProjects' => $userProjects,
        ];
    }

    /**
     * Updates the current user's basic profile information (name, phone,
     * username/email). Validates the email and, when the email changed,
     * ensures it is not already taken.
     *
     * Returns a status code the controller maps to a notification:
     *   - 'success'        profile saved
     *   - 'user_exists'    new email already belongs to another account
     *   - 'no_valid_email' email is not a valid address
     *   - 'enter_email'    email was empty
     *
     * @param  int  $userId  The id of the user being edited.
     * @param  array<string, mixed>  $post  Raw request input (firstname, lastname, user, phone).
     * @return string Status code described above.
     *
     * @api
     */
    public function saveOwnProfile(int $userId, array $post): string
    {
        $row = $this->getUser($userId);

        $values = [
            'firstname' => ($post['firstname']) ?? $row['firstname'],
            'lastname' => ($post['lastname']) ?? $row['lastname'],
            'user' => ($post['user']) ?? $row['username'],
            'phone' => ($post['phone']) ?? $row['phone'],
            'notifications' => $row['notifications'],
            'twoFAEnabled' => $row['twoFAEnabled'],
        ];

        if ($values['user'] === '') {
            return 'enter_email';
        }

        if (! filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
            return 'no_valid_email';
        }

        $changedEmail = $row['username'] !== $values['user'];

        if ($changedEmail && $this->usernameExist($values['user'], $userId) !== false) {
            return 'user_exists';
        }

        $this->editOwn($values, $userId);

        return 'success';
    }

    /**
     * Changes the current user's password after validating the current
     * password, the confirmation match, and the strength requirements.
     *
     * Returns a status code the controller maps to a notification:
     *   - 'success'                    password changed
     *   - 'password_not_strong_enough' new password failed strength check
     *   - 'passwords_dont_match'       new and confirmation differed
     *   - 'previous_password_incorrect' current password was wrong
     *
     * @param  int  $userId  The id of the user being edited.
     * @param  string  $currentPassword  The user's current password.
     * @param  string  $newPassword  The desired new password.
     * @param  string  $confirmPassword  Confirmation of the new password.
     * @return string Status code described above.
     *
     * @api
     */
    public function changeOwnPassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): string
    {
        $row = $this->getUser($userId);

        if (! password_verify($currentPassword, $row['password'])) {
            return 'previous_password_incorrect';
        }

        if ($newPassword !== $confirmPassword) {
            return 'passwords_dont_match';
        }

        if (! $this->checkPasswordStrength($newPassword)) {
            return 'password_not_strong_enough';
        }

        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'password' => $newPassword,
            'notifications' => $row['notifications'],
            'twoFAEnabled' => $row['twoFAEnabled'],
        ];

        $this->userRepo->editOwn($values, $userId);

        return 'success';
    }

    /**
     * Persists the current user's appearance (theme) settings and applies the
     * matching theme/cache side effects so the change takes effect immediately.
     *
     * @param  int  $userId  The id of the user being edited.
     * @param  array<string, mixed>  $post  Raw request input (theme, colormode, colorscheme, themeFont).
     *
     * @api
     */
    public function saveOwnAppearanceSettings(int $userId, array $post): void
    {
        $postTheme = htmlentities($post['theme'] ?? 'default');
        $postColorMode = htmlentities($post['colormode'] ?? 'light');
        $postColorScheme = htmlentities($post['colorscheme'] ?? 'themeDefault');
        $themeFont = htmlentities($post['themeFont'] ?? '');

        $this->settingsService->saveSetting('usersettings.'.$userId.'.theme', $postTheme);
        $this->settingsService->saveSetting('usersettings.'.$userId.'.colorMode', $postColorMode);
        $this->settingsService->saveSetting('usersettings.'.$userId.'.colorScheme', $postColorScheme);
        $this->settingsService->saveSetting('usersettings.'.$userId.'.themeFont', $themeFont);

        $this->themeCore::clearCache();
        $this->themeCore->setActive($postTheme);
        $this->themeCore->setColorMode($postColorMode);
        $this->themeCore->setColorScheme($postColorScheme);
        $this->themeCore->setFont($themeFont);
    }

    /**
     * Persists the current user's locale settings (language, date/time
     * formats, timezone) and applies the session + language side effects so
     * the change takes effect on the next request.
     *
     * @param  int  $userId  The id of the user being edited.
     * @param  array<string, mixed>  $post  Raw request input (language, date_format, time_format, timezone).
     *
     * @api
     */
    public function saveOwnLocaleSettings(int $userId, array $post): void
    {
        $postLang = htmlentities($post['language'] ?? '');
        $dateFormat = htmlentities($post['date_format'] ?? '');
        $timeFormat = htmlentities($post['time_format'] ?? '');
        $tz = htmlentities($post['timezone'] ?? '');

        $this->settingsService->saveSetting('usersettings.'.$userId.'.language', $postLang);
        $this->settingsService->saveSetting('usersettings.'.$userId.'.date_format', $dateFormat);
        $this->settingsService->saveSetting('usersettings.'.$userId.'.time_format', $timeFormat);
        $this->settingsService->saveSetting('usersettings.'.$userId.'.timezone', $tz);

        session()->forget('cache.language_resources_'.$this->language->getCurrentLanguage());

        session(['usersettings.date_format' => $dateFormat]);
        session(['usersettings.time_format' => $timeFormat]);
        session(['usersettings.timezone' => $tz]);

        // Clear the localization cache so middleware re-fetches on next request
        session()->forget('localization.cached');

        $this->language->setLanguage($postLang);
    }

    /**
     * Persists the current user's notification preferences: the master
     * notifications toggle, message frequency, the enabled event-type
     * categories (validated against the known category keys) and the
     * per-project notification levels (validated against the known relevance
     * levels). Cleans up the legacy muted-projects format when present.
     *
     * @param  int  $userId  The id of the user being edited.
     * @param  array<string, mixed>  $post  Raw request input.
     *
     * @api
     */
    public function saveOwnNotificationPreferences(int $userId, array $post): void
    {
        $row = $this->getUser($userId);

        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'notifications' => isset($post['notifications']) ? 1 : 0,
            'twoFAEnabled' => $row['twoFAEnabled'],
        ];

        $this->userRepo->editOwn($values, $userId);

        // Storing option messagefrequency
        $this->settingsService->saveSetting('usersettings.'.$userId.'.messageFrequency', (int) ($post['messagesfrequency'] ?? 3600));

        // Save event-type preferences
        $enabledEventTypes = $post['enabledEventTypes'] ?? [];
        if (! is_array($enabledEventTypes)) {
            $enabledEventTypes = [];
        }
        $validCategories = array_keys(Notification::NOTIFICATION_CATEGORIES);
        $enabledEventTypes = array_values(array_intersect($enabledEventTypes, $validCategories));
        $this->settingsService->saveSetting(
            'usersettings.'.$userId.'.notificationEventTypes',
            json_encode($enabledEventTypes)
        );

        // Save per-project notification levels
        $projectLevels = $post['projectNotificationLevel'] ?? [];
        if (! is_array($projectLevels)) {
            $projectLevels = [];
        }
        $validatedLevels = [];
        foreach ($projectLevels as $projectId => $level) {
            if (Notification::isValidRelevanceLevel($level)) {
                $validatedLevels[(int) $projectId] = $level;
            }
        }
        $this->settingsService->saveSetting(
            'usersettings.'.$userId.'.projectNotificationLevels',
            json_encode($validatedLevels)
        );

        // Clean up old format if it exists
        $oldSetting = $this->settingsService->getSetting('usersettings.'.$userId.'.projectMutedNotifications');
        if ($oldSetting !== false && $oldSetting !== null) {
            $this->settingsService->saveSetting(
                'usersettings.'.$userId.'.projectMutedNotifications',
                ''
            );
        }
    }

    /**
     * Returns the list of supported varying date-time formats used by the
     * profile settings screen.
     *
     * @link https://www.php.net/manual/en/class.datetimeinterface.php#datetimeinterface.constants.types
     *
     * @return array<string, array<int, string>> Format groups keyed by 'dates'/'times'.
     *
     * @api
     */
    public function getSupportedDateTimeFormats(): array
    {
        return [
            'dates' => [
                $this->language->__('language.dateformat'),
                'Y-m-d',
                'D, d M y',
                'l, d-M-y',
                'd.m.Y',
                'd/m/Y',
                'd. F Y',
                'm-d-Y',
                'dmY',
                'F d, Y',
                'd F Y',
            ],
            'times' => [
                $this->language->__('language.timeformat'),
                'H:i P',
                'H:i O',
                'H:i T',
                'H:i:s',
                'H:i',
            ],
        ];
    }

    /**
     * Loads per-project notification levels for the given user.
     *
     * Performs lazy migration from the old binary mute format
     * (projectMutedNotifications: JSON array of project IDs)
     * to the new three-level format
     * (projectNotificationLevels: JSON map of projectId -> relevance level).
     *
     * @param  int  $userId  The id of the user whose levels are loaded.
     * @return array<int, string> Map of project ID to relevance level.
     */
    private function loadProjectNotificationLevels(int $userId): array
    {
        $newSetting = $this->settingsService->getSetting('usersettings.'.$userId.'.projectNotificationLevels');
        if ($newSetting) {
            $decoded = json_decode($newSetting, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Lazy migration: convert old muted-projects array to new format
        $oldSetting = $this->settingsService->getSetting('usersettings.'.$userId.'.projectMutedNotifications');
        if ($oldSetting) {
            $mutedIds = json_decode($oldSetting, true);
            if (is_array($mutedIds) && count($mutedIds) > 0) {
                $migrated = [];
                foreach ($mutedIds as $projectId) {
                    $migrated[(int) $projectId] = Notification::RELEVANCE_MUTED;
                }
                // Save in new format
                $this->settingsService->saveSetting(
                    'usersettings.'.$userId.'.projectNotificationLevels',
                    json_encode($migrated)
                );
                // Clear old format
                $this->settingsService->saveSetting(
                    'usersettings.'.$userId.'.projectMutedNotifications',
                    ''
                );

                return $migrated;
            }
        }

        return [];
    }

    /**
     * Validates a user-update payload for the admin "edit user" screen.
     *
     * Returns a status code the controller maps to a notification:
     *   - 'valid'                  the update may proceed
     *   - 'passwords_dont_match'   username/email was empty (legacy key)
     *   - 'enter_email'            password and confirmation differed (legacy key)
     *   - 'no_valid_email'         email is not a valid address
     *   - 'user_exists'            new email already belongs to another account
     *
     * Note: the notification keys above intentionally mirror the original
     * controller behavior to preserve identical messaging.
     *
     * @param  array<string, mixed>  $values  Shaped values for the update.
     * @param  array<string, mixed>  $row  The current stored user row.
     * @param  int  $id  The id of the user being edited.
     * @param  array<string, mixed>  $post  Raw request input (for password comparison).
     * @return string Status code described above.
     *
     * @api
     */
    public function validateUserUpdate(array $values, array $row, int $id, array $post): string
    {
        if ($values['user'] === '') {
            return 'passwords_dont_match';
        }

        if (isset($post['password']) && ($post['password'] ?? null) != ($post['password2'] ?? null)) {
            return 'enter_email';
        }

        if (! filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
            return 'no_valid_email';
        }

        if ($row['username'] != $values['user'] && $this->usernameExist($row['username'], $id)) {
            return 'user_exists';
        }

        return 'valid';
    }

    /**
     * Updates a user (admin edit screen) and reconciles their project
     * relations based on the posted project list.
     *
     * @param  array<string, mixed>  $values  Shaped values for the update.
     * @param  int  $id  The id of the user being edited.
     * @param  array<int, mixed>|null  $projects  Posted project ids, or null when none submitted.
     *
     * @api
     */
    public function updateUser(array $values, int $id, ?array $projects): void
    {
        $this->editUser($values, $id);
        $this->reconcileProjectRelations($id, $projects);
    }

    /**
     * Reconciles a user's project relations from a posted project list.
     *
     * When no projects are posted, or the sentinel value '0' is the first
     * entry, all relations are removed; otherwise the posted set is saved.
     *
     * @param  int  $userId  The id of the user.
     * @param  array<int, mixed>|null  $projects  Posted project ids, or null when none submitted.
     */
    private function reconcileProjectRelations(int $userId, ?array $projects): void
    {
        if (is_array($projects) && isset($projects[0]) && $projects[0] !== '0') {
            $this->projectService->editUserProjectRelations($userId, $projects);

            return;
        }

        $this->projectService->deleteAllUserProjectRelations($userId);
    }

    /**
     * Returns the flat list of project ids a user is assigned to.
     *
     * @param  int  $userId  The id of the user.
     * @return array<int, mixed> The project ids.
     *
     * @api
     */
    public function getUserProjectIds(int $userId): array
    {
        $projects = $this->projectService->getUserProjectRelation($userId);
        $projectIds = [];

        foreach ($projects as $project) {
            $projectIds[] = $project['projectId'];
        }

        return $projectIds;
    }

    /**
     * Resends an invitation to a pending user.
     *
     * Enforces a 240 second rate-limit (per user, via the session), generates
     * a password-reset/invite code when the user does not yet have one, and
     * sends the invitation email.
     *
     * Returns a status code the controller maps to a notification:
     *   - 'sent'      invitation sent
     *   - 'too_soon'  another invite was sent within the rate-limit window
     *
     * @param  int  $id  The id of the user to re-invite.
     * @param  array<string, mixed>  $row  The current stored user row.
     * @return string Status code described above.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function resendUserInvite(int $id, array $row): string
    {
        if (session()->exists('lastInvite.'.$id) && session('lastInvite.'.$id) >= time() - 240) {
            return 'too_soon';
        }

        session(['lastInvite.'.$id => time()]);

        $pwReset = $row['pwReset'] ?? '';
        if (empty($pwReset)) {
            $pwReset = Uuid::uuid4()->toString();
            $this->patchUser($id, ['pwReset' => $pwReset]);
        }

        $this->sendUserInvite(
            inviteCode: $pwReset,
            user: $row['username']
        );

        return 'sent';
    }

    /**
     * Invites a brand new user from the admin "new user" screen.
     *
     * Owns the values shaping (including the manager client-id decision), the
     * email/username validation, the invite creation, and the project-relation
     * reconciliation.
     *
     * Returns a status code the controller maps to a notification:
     *   - 'success'        user invited
     *   - 'enter_email'    email was empty
     *   - 'no_valid_email' email is not a valid address
     *   - 'user_exists'    email already belongs to another account
     *
     * @param  array<string, mixed>  $post  Raw request input.
     * @param  int|string|null  $sessionClientId  The session user's client id (used for managers).
     * @param  bool  $isManager  Whether the inviting user holds the manager role.
     * @return string Status code described above.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function inviteNewUser(array $post, int|string|null $sessionClientId, bool $isManager): string
    {
        $values = [
            'firstname' => $post['firstname'] ?? '',
            'lastname' => $post['lastname'] ?? '',
            'user' => $post['user'] ?? '',
            'phone' => $post['phone'] ?? '',
            'role' => $post['role'] ?? '',
            'password' => '',
            'pwReset' => '',
            'status' => '',
            'jobTitle' => $post['jobTitle'] ?? '',
            'jobLevel' => $post['jobLevel'] ?? '',
            'department' => $post['department'] ?? '',
            'clientId' => $isManager ? $sessionClientId : ($post['client'] ?? ''),
        ];

        if ($values['user'] === '') {
            return 'enter_email';
        }

        if (! filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
            return 'no_valid_email';
        }

        if ($this->usernameExist($values['user'])) {
            return 'user_exists';
        }

        $userId = $this->createUserInvite($values);

        $projects = $post['projects'] ?? null;
        if (is_array($projects) && count($projects) > 0) {
            $this->reconcileProjectRelations((int) $userId, $projects);
        }

        return 'success';
    }

    /**
     * Connects/binds to LDAP and returns the directory members for the import
     * dialog. Returns false when the bind fails.
     *
     * @param  string  $bindUsername  The directory username to bind with.
     * @param  string  $password  The password to bind with.
     * @return array<int, array<string, mixed>>|false Directory members, or false on failed bind.
     *
     * @api
     */
    public function fetchLdapMembers(string $bindUsername, string $password): array|false
    {
        $ldapService = app()->make(LdapService::class);
        $ldapService->connect();

        if (! $ldapService->bind($bindUsername, $password)) {
            return false;
        }

        return $ldapService->getAllMembers() ?: [];
    }

    /**
     * Imports/updates the LDAP users selected from the staged member list.
     *
     * @param  array<int, array<string, mixed>>  $stagedUsers  The full staged member list.
     * @param  array<int, string>  $selectedUsernames  The usernames the admin selected for import.
     *
     * @api
     */
    public function importSelectedLdapUsers(array $stagedUsers, array $selectedUsernames): void
    {
        $users = [];
        foreach ($stagedUsers as $user) {
            if (array_search($user['username'], $selectedUsernames)) {
                $users[] = $user;
            }
        }

        app()->make(LdapService::class)->upsertUsers($users);
    }
}
