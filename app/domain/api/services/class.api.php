<?php

namespace leantime\domain\services;

    use Exception;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\eventhelpers;
    use Ramsey\Uuid\Uuid;
    use RobThree\Auth\TwoFactorAuth;

    class api
    {
        use eventhelpers;


        /**
         * __construct
         *
         */
        public function __construct()
        {
            $this->apiRepository = new repositories\api();
            $this->userRepo = new repositories\users();
        }

        public function getAPIKeyUser($apiKey): bool|array
        {

            //Split apiKey into parts
            $apiKeyParts = explode("_", $apiKey);

            if(!is_array($apiKeyParts) || count($apiKeyParts) != 3) {
                error_log("Not a valid API Key format");
                return false;
            }

            $namespace = $apiKeyParts[0];
            $user = $apiKeyParts[1];
            $key = $apiKeyParts[2];

            if($namespace != "lt") {
                error_log("Unknown namespace for API request");
                return false;
            }

            $apiUser = $this->apiRepository->getAPIKeyUser($user);

            if(password_verify($key, $apiUser['password'])){
                return $apiUser;
            }

            return false;

        }

        /**
         * createAPIKey - simple service wrapper to create a new user
         *
         * TODO: Should accept userModel
         *
         * @access public
         * @param  array $values basic user values
         * @return bool|array returns new user id on success, false on failure
         */
        public function createAPIKey(array $values): bool|array
        {

            $user = $this->random_str(32);
            $password = $this->random_str(32);

            $values["user"] = $user;
            $values["lastname"] = '';
            $values["passwordClean"] = $password;
            $values["password"] = password_hash($password, PASSWORD_DEFAULT);
            $values["status"] = 'a';
            $values["clientId"] = '';
            $values["phone"] = '';
            $values["id"] = $this->userRepo->addUser($values);

            if($values["id"]) {
                return $values;
            }else{
                return false;
            }
        }

        /**
         * getAPIKeys - gets api keys (users) from user table
         *
         *
         * @access public
         * @return array|false
         */
        public function getAPIKeys(){
            $keys =  $this->userRepo->getAllBySource("api");

            foreach($keys as &$key) {
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
         * @param int $length      How many characters do we want?
         * @param string $keyspace A string of all possible characters
         *                         to select from
         * @return string
         */
        public function random_str(
            int $length = 64,
            string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        ): string {
            if ($length < 1) {
                throw new \RangeException("Length must be a positive integer");
            }
            $pieces = [];
            $max = mb_strlen($keyspace, '8bit') - 1;
            for ($i = 0; $i < $length; ++$i) {
                $pieces []= $keyspace[random_int(0, $max)];
            }
            return implode('', $pieces);
        }


}
