<?php

namespace Leantime\Domain\Users\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Files\Repositories\Files;
use PDO;
use SVG\SVG;

class Users
{
    public string $user;

    public string $lastname;

    public string $firstname;

    public int $role;

    public string $jobTitle;

    public string $jobLevel;

    public string $department;

    public int $id;

    public array $adminRoles = [40, 50];

    public array $status = ['active' => 'label.active', 'inactive' => 'label.inactive', 'invited' => 'label.invited'];

    /**
     * __construct - neu db connection
     */
    public function __construct(
        protected Environment $config,
        protected DbCore $db,
        protected Avatarcreator $avatarcreator,
        protected Files $files
    ) {}

    /**
     * getUser - get on user from db
     */
    public function getUser($id): array|bool
    {

        $sql = 'SELECT * FROM `zp_user` WHERE id = :id LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':id', $id, PDO::PARAM_STR);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * getUser - get on user from db
     *
     * @return mixed
     */
    public function getUserBySha($hash): array|false
    {

        $sql = 'SELECT * FROM `zp_user` WHERE SHA1(CONCAT(id,:sessionSecret)) = :hash';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':hash', $hash, PDO::PARAM_STR);
        $stmn->bindValue(':sessionSecret', $this->config->sessionPassword, PDO::PARAM_STR);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * getLastLogin - get the date of the last login of any user
     *
     * @return string|null returns datetime string with last login or null if nothing could be found
     */
    public function getLastLogin(): ?string
    {

        $sql = 'SELECT  lastlogin FROM `zp_user` Order by lastlogin DESC LIMIT 1';

        $stmn = $this->db->database->query($sql);
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values['lastlogin'] ?? null;
    }

    /**
     * getUserByEmail - get on user from db
     */
    public function getUserByEmail(string $email, string $status = 'a'): array|false
    {
        $sql = 'SELECT * FROM `zp_user` WHERE username = :email ';

        if ($status === 'a') {
            $sql .= " and LOWER(status) = 'a'";
        }

        if ($status === 'i') {
            $sql .= " and LOWER(status) = 'i'";
        }

        $sql .= ' LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':email', $email, PDO::PARAM_STR);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }

    public function getNumberOfUsers($activeOnly = false, $includeApi = true): int
    {
        $sql = 'SELECT COUNT(id) AS userCount FROM `zp_user`';
        $conditions = [];

        if ($activeOnly) {
            $conditions[] = "status = 'a'";
        }

        if ($includeApi) {
            $conditions[] = "(source != 'api' OR source IS NULL)";
        }

        foreach ($conditions as $condition) {
            $sql .= str_contains($sql, 'WHERE') ? ' AND' : ' WHERE';
            $sql .= " $condition";
        }

        $stmn = $this->db->database->query($sql);
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values['userCount'] ?? 0;
    }

    /**
     * getEmployees - get all employees
     */
    public function getEmployees(): array
    {

        $sql = 'SELECT
            zp_user.id,
            IF(zp_user.firstname IS NOT NULL, zp_user.firstname, zp_user.username) AS firstname,
            zp_user.lastname,
            zp_user.jobTitle,
            zp_user.jobLevel,
            zp_user.department,
            zp_user.modified
         FROM zp_user
            ORDER BY lastname';

        $stmn = $this->db->database->query($sql);
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * getAll - get all user
     *
     * @param  bool  $activeOnly
     */
    public function getAll($activeOnly = false): array
    {

        $query = "SELECT
                      zp_user.id,
                      lastname,
                      IF(firstname <> '', firstname, username) AS firstname,
                      role,
                      profileId,
                      status,
                      username,
                      twoFAEnabled,
                      clientId,
                      zp_clients.name AS clientName,
                      jobTitle,
                      jobLevel,
                      department,
                      zp_user.modified
                    FROM `zp_user`
                    LEFT JOIN zp_clients ON zp_clients.id = zp_user.clientId
                    WHERE !(source <=> 'api')";

        if ($activeOnly) {
            $query .= " AND status LIKE 'a' ";
        }

        $query .= ' ORDER BY lastname';

        $stmn = $this->db->database->query($query);
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    public function getAllBySource($source): false|array
    {

        $query = 'SELECT
                      zp_user.id,
                      lastname,
                      firstname,
                      role,
                      profileId,
                      status,
                      username,
                      lastlogin,
                      createdOn,
                      jobTitle,
                      jobLevel,
                      department,
                      modified
                    FROM `zp_user`
                    WHERE source <=> :source
                    ORDER BY lastname';

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':source', $source, PDO::PARAM_STR);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * getAll - get all user
     */
    public function getAllClientUsers($clientId): array
    {

        $query = 'SELECT
                        zp_user.id,
                        lastname,
                        firstname,
                        role,
                        profileId,
                        status,
                        username,
                        twoFAEnabled,
                        zp_clients.name AS clientName,
                        jobTitle,
                        jobLevel,
                        department,
                        modified
                    FROM `zp_user`
                    LEFT JOIN zp_clients ON zp_clients.id = zp_user.clientId
                    WHERE clientId = :clientId
                    ORDER BY lastname';

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':clientId', $clientId, PDO::PARAM_STR);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    public function isAdmin($userId): bool
    {

        $sql = 'SELECT role FROM zp_user WHERE id = :id LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':id', $userId, PDO::PARAM_STR);

        $stmn->execute();
        $user = $stmn->fetch();
        $stmn->closeCursor();

        $flag = false;
        if (in_array($user['role'], $this->adminRoles)) {
            $flag = true;
        }

        return $flag;
    }

    /**
     * editUSer - edit user
     */
    public function editUser(array $values, $id): bool
    {
        if (isset($values['password']) && $values['password'] != '') {
            $chgPW = ' password = :password, ';
        } else {
            $chgPW = '';
        }

        $query = 'UPDATE `zp_user` SET
                firstname = :firstname,
                lastname = :lastname,
                username = :username,
                phone = :phone,
                status = :status,
                role = :role,
                hours = :hours,
                wage = :wage,
                jobTitle = :jobTitle,
                jobLevel = :jobLevel,
                department = :department,
                '.$chgPW.'
                clientId = :clientId,
                modified = :modified
             WHERE id = :id LIMIT 1';

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
        $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
        $stmn->bindValue(':username', $values['user'], PDO::PARAM_STR);
        $stmn->bindValue(':phone', $values['phone'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
        $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);
        $stmn->bindValue(':hours', $values['hours'] ?? 0, PDO::PARAM_STR);
        $stmn->bindValue(':wage', $values['wage'] ?? 0, PDO::PARAM_STR);
        $stmn->bindValue(':clientId', $values['clientId'], PDO::PARAM_STR);
        $stmn->bindValue(':jobTitle', $values['jobTitle'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':jobLevel', $values['jobLevel'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':department', $values['department'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);

        $stmn->bindValue(':id', $id, PDO::PARAM_STR);

        if (isset($values['password']) && $values['password'] != '') {
            $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
        }

        $result = $stmn->execute();
        $stmn->closeCursor();

        return $result;
    }

    /**
     * usernameExist - Check if a username is already in db
     */
    public function usernameExist($username, string $userId = ''): bool
    {

        if ($userId != '') {
            $queryOwn = ' AND id != :id ';
        } else {
            $queryOwn = '';
        }

        $query = 'SELECT COUNT(username) AS numUser FROM `zp_user` WHERE username = :username '.$queryOwn.' LIMIT 1';

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':username', $username, PDO::PARAM_STR);

        if ($userId !== '') {
            $stmn->bindValue(':id', $userId, PDO::PARAM_STR);
        }

        $stmn->execute();

        $result = $stmn->fetch();
        $stmn->closeCursor();

        return (int) $result['numUser'] === 1;
    }

    /**
     * editOwn - Edit own Userdates
     */
    public function editOwn($values, $id): void
    {

        if (isset($values['password']) && $values['password'] !== '') {
            $chgPW = ' password = :password, ';
        } else {
            $chgPW = '';
        }

        $query = 'UPDATE `zp_user` SET
                lastname = :lastname,
                firstname = :firstname,
                username = :username,
                '.$chgPW.'
                phone = :phone,
                notifications = :notifications,
                modified = :modified
                WHERE id = :id LIMIT 1';

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
        $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
        $stmn->bindValue(':username', $values['user'], PDO::PARAM_STR);
        $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
        $stmn->bindValue(':notifications', $values['notifications'], PDO::PARAM_STR);
        $stmn->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);

        $stmn->bindValue(':id', $id, PDO::PARAM_STR);

        if (isset($values['password']) && $values['password'] != '') {
            $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
        }

        $stmn->execute();
        $stmn->closeCursor();
    }

    /**
     * addUser - add User to db
     */
    public function addUser(array $values): false|string
    {

        $query = 'INSERT INTO `zp_user` (
                            firstname,
                            lastname,
                            phone,
                            username,
                            role,
                            notifications,
                            clientId,
                            password,
                            source,
                            pwReset,
                            status,
                            createdOn,
                            jobTitle,
                            jobLevel,
                            department,
                            modified
                        ) VALUES (
                            :firstname,
                            :lastname,
                            :phone,
                            :user,
                            :role,
                            1,
                            :clientId,
                            :password,
                            :source,
                            :pwReset,
                            :status,
                            :createdOn,
                            :jobTitle,
                            :jobLevel,
                            :department,
                            :modified
                        )';

        $stmn = $this->db->database->prepare($query);

        $stmn->bindValue(':firstname', $values['firstname'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':lastname', $values['lastname'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':phone', $values['phone'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':user', $values['user'], PDO::PARAM_STR);
        $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);

        $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
        $stmn->bindValue(':clientId', $values['clientId'] ?? '', PDO::PARAM_INT);

        $stmn->bindValue(':jobTitle', $values['jobTitle'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':jobLevel', $values['jobLevel'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':department', $values['department'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':createdOn', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmn->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);

        if (isset($values['source'])) {
            $stmn->bindValue(':source', $values['source'], PDO::PARAM_STR);
        } else {
            $stmn->bindValue(':source', '', PDO::PARAM_STR);
        }

        $stmn->bindValue(':pwReset', $values['pwReset'] ?? '', PDO::PARAM_STR);
        $stmn->bindValue(':status', $values['status'] ?? '', PDO::PARAM_STR);

        $stmn->execute();
        $userId = $this->db->database->lastInsertId();

        $stmn->closeCursor();

        return $userId;
    }

    /**
     * deleteUser - delete user from db
     */
    public function deleteUser($id): void
    {

        $query = 'DELETE FROM `zp_user` WHERE zp_user.id = :id';

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':id', $id, PDO::PARAM_STR);

        $stmn->execute();
        $stmn->closeCursor();
    }

    /**
     * setPicture - set the profile picture for an individual
     *
     * @throws BindingResolutionException
     */
    public function setPicture(array $_FILE, $id): void
    {

        $sql = 'SELECT * FROM `zp_user` WHERE id=:id';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':id', $id, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        if (isset($values['profileId']) && $values['profileId'] > 0) {

            $file = $this->files->getFile($values['profileId']);
            if (is_array($file)) {
                $img = 'userdata/'.$file['encName'].$file['extension'];
                $this->files->deleteFile($values['profileId']);
            }
        }

        $lastId = $this->files->upload($_FILE, 'user', $id);

        if (isset($lastId['fileId'])) {
            $sql = 'UPDATE
                            `zp_user`
                        SET
                            profileId = :fileId,
                            modified = :modified
                        WHERE id = :userId';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':fileId', $lastId['fileId'], PDO::PARAM_INT);
            $stmn->bindValue(':userId', $id, PDO::PARAM_INT);
            $stmn->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }
    }

    /**
     * @return string[]|SVG
     *
     * @throws BindingResolutionException
     */
    public function getProfilePicture($id): array|SVG
    {
        $value = false;
        if ($id !== false) {
            $sql = 'SELECT profileId, firstname, lastname FROM `zp_user` WHERE id = :id LIMIT 1';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $value = $stmn->fetch();
            $stmn->closeCursor();
        }

        // If can't find user, return ghost
        if (empty($value)) {
            $avatar = $this->avatarcreator->getAvatar('👻');

            return ['filename' => $avatar, 'type' => 'generated'];
        }

        // If user uploaded return uploaded file
        if (! empty($value['profileId'])) {

            $file = $this->files->getFile($value['profileId']);

            if ($file) {
                $filePath = $file['encName'].'.'.$file['extension'];
                $type = $file['extension'];

                return ['filename' => $filePath, 'type' => 'uploaded'];
            }

        }

        // Otherwise return avatar
        $name = $value['firstname'].' '.$value['lastname'];

        $avatar = $this->avatarcreator->getAvatar($name);

        if (is_string($avatar)) {
            return ['filename' => $avatar, 'type' => 'generated'];
        }

        return $avatar;

    }

    public function patchUser($id, $params): bool
    {

        $sql = 'UPDATE zp_user SET';

        foreach ($params as $key => $value) {
            $sql .= ' '.DbCore::sanitizeToColumnString($key).'=:'.DbCore::sanitizeToColumnString($key).', ';
        }

        $sql .= ' modified =:modified WHERE id=:id LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':id', $id, PDO::PARAM_STR);
        $stmn->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);

        foreach ($params as $key => $value) {
            $cleanKey = DbCore::sanitizeToColumnString($key);
            $val = $value;
            if ($cleanKey === 'password') {
                $val = password_hash($value, PASSWORD_DEFAULT);
            }
            $stmn->bindValue(':'.$cleanKey, $val, PDO::PARAM_STR);
        }

        $return = $stmn->execute();
        $stmn->closeCursor();

        return $return;
    }

    /**
     * getUserIdByName - Get Author/User Id by first- and lastname
     *
     * @param  string  $lastname  Lastname
     * @return int|bool Identifier of user or false, if not found
     */
    public function getUserIdByName(string $firstname, string $lastname): int|bool
    {
        $query = 'SELECT profileId FROM `zp_user` WHERE `firstname` = :firstname AND `lastname` = :lastname';

        $stmn = $this->db->database->prepare($query);

        $stmn->bindValue(':firstname', $firstname, PDO::PARAM_STR);
        $stmn->bindValue(':lastname', $lastname, PDO::PARAM_STR);

        $stmn->execute();
        $result = $stmn->fetch();
        $stmn->closeCursor();

        return $result['profileId'] ?? false;
    }

    /**
     * Get user settings - retrieves and deserializes user settings
     *
     * @param  int  $userId  The user ID to get settings for
     * @param  string|null  $settingPath  Optional dot notation path to retrieve specific setting (e.g. 'onboarding.firstLoginCompleted')
     * @return mixed The requested settings or specific setting value, empty array if no settings exist
     */
    public function getUserSettings(int $userId, ?string $settingPath = null): mixed
    {
        $sql = 'SELECT settings FROM `zp_user` WHERE id = :id LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':id', $userId, PDO::PARAM_INT);

        $stmn->execute();
        $result = $stmn->fetch();
        $stmn->closeCursor();

        // If no settings exist yet, return empty array
        if (! $result || empty($result['settings'])) {
            return [];
        }

        // Try to unserialize the settings
        try {
            $settings = unserialize($result['settings']);

            // If we have a specific path to retrieve
            if ($settingPath !== null) {
                return $this->getNestedSetting($settings, $settingPath);
            }

            return $settings;
        } catch (\Exception $e) {
            // If there's an error unserializing, return empty array
            return [];
        }
    }

    /**
     * Helper method to get a nested setting using dot notation
     *
     * @param  array  $settings  The settings array
     * @param  string  $path  Dot notation path (e.g. 'onboarding.firstLoginCompleted')
     * @return mixed The setting value or null if not found
     */
    private function getNestedSetting(array $settings, string $path)
    {
        $keys = explode('.', $path);
        $current = $settings;

        foreach ($keys as $key) {
            if (! is_array($current) || ! isset($current[$key])) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }
}
