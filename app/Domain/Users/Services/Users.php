<?php

namespace Leantime\Domain\Users\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
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
        protected Avatarcreator $avatarcreator
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
     * @api
     */
    public function getUser($id): array|bool
    {
        return $this->userRepo->getUser($id);
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
     * @api
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
     * getUsersWithProjectAccess - gets all users who can access a project
     *
     * TODO: Should return usermodel
     *
     * @param  int  $currentUser  user who is trying to access the project
     * @param  int  $projectId  project id
     * @return array returns array of users
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getUsersWithProjectAccess(int $currentUser, int $projectId): array
    {
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
}
