<?php

namespace Leantime\Domain\Auth\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Theme;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Ldap\Services\Ldap;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use RobThree\Auth\TwoFactorAuth;

/**
 *
 */
class Auth
{
    use DispatchesEvents;

    /**
     * @var int|null user id from DB
     */
    private ?int $userId = null;

    /**
     * @var int|null user id from DB
     */
    private ?int $clientId = null;

    /**
     * @var string|null username from db
     */
    private ?string $username = null;

    /**
     * @var string username from db
     */
    private string $name = '';

    /**
     * @var string profileid (image) from db
     */
    private string $profileId = '';

    /**
     * @var string|null
     */
    private ?string $password = null;

    /**
     * @var string|null username (emailaddress)
     */
    private ?string $user = null;

    /**
     * @var string|null username (emailaddress)
     */
    private ?string $mail = null;

    /**
     * @var bool $twoFAEnabled
     */
    private bool $twoFAEnabled;

    /**
     * @var string $twoFASecret
     */
    private string $twoFASecret;

    /**
     * @var string|null
     */
    private SessionManager|null $session = null;

    /**
     * @var string userrole (admin, client, employee)
     */
    public string $role = '';

    public array $settings = array();

    /**
     * @var int time for cookie
     */
    public mixed $cookieTime;

    /**
     * @var string
     */
    public string $error = "";

    /**
     * @var string
     */
    public string $success = "";

    /**
     * @var string|bool
     */
    public string|bool $resetInProgress = false;

    /**
     * How often can a user reset a password before it has to be changed
     *
     * @var int
     */
    public int $pwResetLimit = 5;

    private EnvironmentCore $config;
    public LanguageCore $language;
    public SettingRepository $settingsRepo;
    public AuthRepository $authRepo;
    public UserRepository $userRepo;

    /**
     * __construct - getInstance of session and get sessionId and refers to login if post is set
     *
     * @param EnvironmentCore   $config
     * @param SessionManager    $session
     * @param LanguageCore      $language
     * @param SettingRepository $settingsRepo
     * @param AuthRepository    $authRepo
     * @param UserRepository    $userRepo
     * @throws BindingResolutionException
     */
    public function __construct(
        EnvironmentCore $config,
        ?SessionManager $session,
        LanguageCore $language,
        SettingRepository $settingsRepo,
        AuthRepository $authRepo,
        UserRepository $userRepo
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->language = $language;
        $this->settingsRepo = $settingsRepo;
        $this->authRepo = $authRepo;
        $this->userRepo = $userRepo;

        $this->cookieTime = $this->config->sessionExpiration;
    }

    /**
     * @param bool $forceGlobalRoleCheck
     *
     * @return string|bool returns role as string or false on failure
     *
     * @throws BindingResolutionException
     */
    public static function getRoleToCheck(bool $forceGlobalRoleCheck): string|bool
    {
        if (session()->exists("userdata") === false) {
            return false;
        }

        if ($forceGlobalRoleCheck) {
            $roleToCheck = session("userdata.role");
            // If projectRole is not defined or if it is set to inherited
        } elseif (!session()->exists("userdata.projectRole") || session("userdata.projectRole") == "inherited" || session("userdata.projectRole") == "") {
            $roleToCheck = session("userdata.role");
            // Do not overwrite admin or owner roles
        } elseif (session("userdata.role") == Roles::$owner || session("userdata.role") == Roles::$admin || session("userdata.role") == Roles::$manager) {
            $roleToCheck = session("userdata.role");
            // In all other cases check the project role
        } else {
            $roleToCheck = session("userdata.projectRole");
        }

        // Ensure the role is a valid role
        if (in_array($roleToCheck, Roles::getRoles()) === false) {
            report("Check for invalid role detected: " . $roleToCheck);
            return false;
        }

        return $roleToCheck;
    }

    /**
     * login - Validate POST-data with DB
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @throws BindingResolutionException
     */
    public function login(string $username, string $password): bool
    {
        self::dispatch_event("beforeLoginCheck", ['username' => $username, 'password' => $password]);

        // different identity providers can live here
        // they all need to
        //// A: ensure the user is in leantime (with a valid role) and if not create the user
        //// B: set the session variables
        //// C: update users from the identity provider,
        // Try Ldap
        if ($this->config->useLdap === true && extension_loaded('ldap')) {
            $ldap = app()->make(Ldap::class);

            if ($ldap->connect() && $ldap->bind($username, $password)) {
                //Update username to include domain
                $usernameWDomain = $ldap->getEmail($username);
                //Get user
                $user = $this->userRepo->getUserByEmail($usernameWDomain);

                $ldapUser = $ldap->getSingleUser($username);

                if ($ldapUser === false) {
                    return false;
                }

                // If user does not exist create user
                if (!$user) {
                    $userArray = array(
                        'firstname' => $ldapUser['firstname'],
                        'lastname' => $ldapUser['lastname'],
                        'phone' => $ldapUser['phone'],
                        'user' => $ldapUser['user'],
                        'role' => $ldapUser['role'],
                        'department' => $ldapUser['department'],
                        'jobTitle'  => $ldapUser['jobTitle'],
                        'jobLevel'  => $ldapUser['jobLevel'],
                        'password' => '',
                        'clientId' => '',
                        'source' => 'ldap',
                        'status' => 'a',
                    );

                    $userId = $this->userRepo->addUser($userArray);

                    if ($userId !== false) {
                        $user = $this->userRepo->getUserByEmail($usernameWDomain);
                    } else {
                        report("Ldap user creation failed.");
                        return false;
                    }

                    // @TODO: create a better login response. This will return that the username or password was not correct
                } else {
                    $user['firstname'] = $ldapUser['firstname'];
                    $user['lastname'] = $ldapUser['lastname'];
                    $user['phone'] = $ldapUser['phone'];
                    $user['user'] = $user['username'];
                    $user['department'] = $ldapUser['department'];
                    $user['jobTitle'] = $ldapUser['jobTitle'];
                    $user['jobLevel']  = $ldapUser['jobLevel'];

                    $this->userRepo->editUser($user, $user['id']);
                }

                if ($user !== false && is_array($user)) {
                    $this->setUserSession($user, true);

                    return true;
                } else {
                    report("Could not retrieve user by email");

                    return false;
                }
            }

            // Don't return false, to allow the standard login provider to check the db for contractors or clients not
            // in ldap
        } elseif ($this->config->useLdap === true && !extension_loaded('ldap')) {
            report("Can't use ldap. Extension not installed");
        }

        // TODO: Single Sign On?
        // Standard login
        // Check if the user is in our db
        // Check even if ldap is turned on to allow contractors and clients to have an account
        $user = $this->authRepo->getUserByLogin($username, $password);

        if ($user !== false && is_array($user)) {
            $this->setUserSession($user);

            self::dispatch_event("afterLoginCheck", ['username' => $username, 'password' => $password, 'authService' => app()->make(self::class)]);

            return true;
        } else {
            $this->logFailedLogin($username);
            self::dispatch_event("afterLoginCheck", ['username' => $username, 'password' => $password, 'authService' => app()->make(self::class)]);

            return false;
        }
    }

    /**
     * @param mixed $user
     * @param bool  $isLdap
     *
     * @return false|void
     *
     * @throws BindingResolutionException
     */
    public function setUsersession(mixed $user, bool $isLdap = false)
    {
        if (!$user || !is_array($user)) {
            return false;
        }

        $currentUser = array(
            "id" => (int)$user['id'],
            "name" => strip_tags($user['firstname']),
            "profileId" => $user['profileId'],
            "mail" => filter_var($user['username'], FILTER_SANITIZE_EMAIL),
            "clientId" => $user['clientId'],
            "role" => Roles::getRoleString($user['role']),
            "settings" => $user['settings'] ? unserialize($user['settings']) : array(),
            "twoFAEnabled" => $user['twoFAEnabled'] ?? false,
            "twoFAVerified" => false,
            "twoFASecret" => $user['twoFASecret'] ?? '',
            "isLdap" => $isLdap,
            "createdOn" => !empty($user['createdOn']) ? dtHelper()->parseDbDateTime($user['createdOn']) : dtHelper()->userNow(),
            "modified" => !empty($user['modified']) ? dtHelper()->parseDbDateTime($user['modified']) : dtHelper()->userNow()
        );

        $currentUser = self::dispatch_filter('user_session_vars', $currentUser);

        session(['userdata' => $currentUser]);

        $this->updateUserSessionDB($currentUser['id'], session()->getId());

        //Clear user theme cache on login
        Theme::clearCache();
    }


    /**
     * @param int    $userId
     * @param string $sessionID
     *
     * @return bool
     */
    public function updateUserSessionDB(int $userId, string $sessionID): bool
    {
        return $this->authRepo->updateUserSession($userId, $sessionID, time());
    }

    /**
     * logged_in - Check if logged in and Update sessions
     *
     * @access public
     *
     * @return bool
     */
    public function loggedIn(): bool
    {
        // Check if we actually have a php session available
        if (session()->exists("userdata")) {
            return true;
            // If the session doesn't have any session data we are out of sync. Start again
        } else {
            return false;
        }
    }

    /**
     * Checks if a user is logged in.
     *
     * @return bool Returns true if the user is logged in, false otherwise.
     */
    public static function isLoggedIn(): bool
    {

        // Check if we actually have a php session available
        if (session()->exists("userdata")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * logout - destroy sessions and cookies
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function logout(): void
    {

        $this->authRepo->invalidateSession($this->session->getId());

        $sessionsToDestroy = self::dispatch_filter('sessions_vars_to_destroy', [
                    'userdata',
                    'template',
                    'subdomainData',
                    'currentProject',
                    'currentSprint',
                    'projectsettings',
                    'currentSubscriptions',
                    'lastTicketView',
                    'lastFilterdTicketTableView',
        ]);

        foreach ($sessionsToDestroy as $key) {
            session()->forget($key);
        }

        self::dispatch_event("afterSessionDestroy", ['authService' => app()->make(self::class)]);

    }

    /**
     * validateResetLink - validates that the password reset link belongs to a user account in the database
     *
     * @param string $hash invite link hash
     *
     * @return bool
     */
    public function validateResetLink(string $hash): bool
    {

        return $this->authRepo->validateResetLink($hash);
    }

    /**
     * getUserByInviteLink - gets the user by invite link
     *
     * @param string $hash invite link hash
     *
     * @return array|bool
     */
    public function getUserByInviteLink(string $hash): bool|array
    {
        return $this->authRepo->getUserByInviteLink($hash);
    }

    /**
     * generateLinkAndSendEmail - generates an invitation link (hash) and sends email to user
     *
     * @param string $username new user to be invited (email)
     *
     * @return bool returns true on success, false on failure
     *
     * @throws BindingResolutionException
     */
    public function generateLinkAndSendEmail(string $username): bool
    {

        $userFromDB = $this->userRepo->getUserByEmail($_POST["username"]);

        if ($userFromDB !== false && count($userFromDB) > 0) {
            if ($userFromDB['pwResetCount'] < $this->pwResetLimit) {
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $resetLink = substr(str_shuffle($permitted_chars), 0, 32);

                $result = $this->authRepo->setPWResetLink($username, $resetLink);

                if ($result) {
                    //Don't queue, send right away
                    $mailer = app()->make(MailerCore::class);
                    $mailer->setContext('password_reset');
                    $mailer->setSubject($this->language->__('email_notifications.password_reset_subject'));
                    $actual_link = "" . BASE_URL . "/auth/resetPw/" . $resetLink;
                    $mailer->setHtml(sprintf($this->language->__('email_notifications.password_reset_message'), $actual_link));
                    $to = array($username);
                    $mailer->sendMail($to, "Leantime System");

                    return true;
                }
            } elseif ($this->config->debug) {
                report(
                    "PW reset failed: maximum request count has been reached for user " . $userFromDB['id']
                );
            }
        }

        return false;
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function changePw(string $password, string $hash): bool
    {
        return $this->authRepo->changePW($password, $hash);
    }

    /**
     * @param string $role
     * @param bool   $forceGlobalRoleCheck
     *
     * @return bool
     *
     * @throws BindingResolutionException
     */
    public static function userIsAtLeast(string $role, bool $forceGlobalRoleCheck = false): bool
    {

        //Force Global Role check to circumvent projectRole checks for global controllers (users, projects, clients etc)
        $roleToCheck = self::getRoleToCheck($forceGlobalRoleCheck);

        if ($roleToCheck === false) {
            return false;
        }

        $testKey = array_search($role, Roles::getRoles());

        if ($role == "" || $testKey === false) {
            report("Check for invalid role detected: " . $role);
            return false;
        }

        $currentUserKey = array_search($roleToCheck, Roles::getRoles());

        if ($testKey <= $currentUserKey) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param array|string $role
     * @param bool         $forceGlobalRoleCheck
     *
     * @return bool
     *
     * @throws HttpResponseException
     */
    public static function authOrRedirect(array|string $role, bool $forceGlobalRoleCheck = false): bool
    {
        if (self::userHasRole($role, $forceGlobalRoleCheck)) {
            return true;
        }

        throw new HttpResponseException(FrontcontrollerCore::redirect(BASE_URL . "/errors/error403"));
    }

    /**
     * @param string|array $role
     * @param bool         $forceGlobalRoleCheck
     *
     * @return bool
     *
     * @throws BindingResolutionException
     */
    public static function userHasRole(string|array $role, bool $forceGlobalRoleCheck = false): bool
    {

        //Force Global Role check to circumvent projectRole checks for global controllers (users, projects, clients etc)
        $roleToCheck = self::getRoleToCheck($forceGlobalRoleCheck);

        if (is_array($role) && in_array($roleToCheck, $role)) {
            return true;
        } elseif ($role == $roleToCheck) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public static function getRole(): void
    {
    }

    /**
     * @return mixed
     */
    public static function getUserClientId(): mixed
    {
        return session("userdata.clientId");
    }

    /**
     * @return mixed
     */
    public static function getUserId(): mixed
    {
        return session("userdata.id");
    }

    /**
     * @return mixed
     */
    public function use2FA(): mixed
    {
        return session("userdata.twoFAEnabled");
    }


    /**
     * @param string $code
     *
     * @return bool
     */
    public function verify2FA(string $code): bool
    {
        $tfa = new TwoFactorAuth('Leantime');

        return $tfa->verifyCode(session("userdata.twoFASecret"), $code);
    }


    /**
     * @return mixed
     */
    public function get2FAVerified(): mixed
    {
        return session("userdata.twoFAVerified");
    }

    /**
     * @return void
     */
    public function set2FAVerified(): void
    {
        session(["userdata.twoFAVerified" => true]);
    }

    /**
     * @param string $user
     *
     * @return void
     */
    private function logFailedLogin(string $user): void
    {
        $user = $user == "" ? "unknown" : $user;
        $date = new \DateTime();
        $date = $date->format("y:m:d h:i:s");

        $ip = $_SERVER['REMOTE_ADDR'];
        $msg = "[" . $date . "][" . $ip . "] Login failed for user: " . $user;

        report($msg);
    }
}
