<?php

namespace leantime\domain\services {

    use leantime\core;
    use pdo;

    class reactions
    {
        /**
         * @access private
         * @var    \leantime\domain\repositories\reactions $reactionsRepo reactions repository
         */
        private \leantime\domain\repositories\reactions $reactionsRepo;



        public function __construct()
        {

            $this->reactionsRepo =  new \leantime\domain\repositories\reactions();
        }

        /**
         * addReaction - adds a reaction to an entity
         * @access public
         *
         * @param string $module
         * @param int $moduleId
         * @param int $userId
         * @param string $reaction
         *
         * @return bool
         */
        public function addReaction(string $module, int $moduleId, int $userId, string $reaction): bool
        {

           return $this->reactionsRepo->addReaction($module, $moduleId, $userId, $reaction);
        }

        /**
         * getGroupedEntityReactions - gets all reactions for a given entity grouped and counted by reactions
         * @access public
         *
         * @param string $module
         * @param int $moduleId
         *
         * @return array|bool returns the array on success or false on failure
         */
        public function getGroupedEntityReactions($module, $moduleId): array|false
        {
            return $this->reactionsRepo->getGroupedEntityReactions($module, $moduleId);
        }

        /**
         * getMyReactions - gets user reactions. Can be very broad or very targeted
         * @access public
         *
         * @param int $userId
         * @param string $module
         * @param ?int $moduleId
         * @param string $reaction
         *
         * @return array|false
         */
        public function getMyReactions(int $userId, string $module = '', ?int $moduleId = null, string $reaction = ''): array|false
        {

            return $this->reactionsRepo->getGroupedEntityReactions($userId, $module, $moduleId, $reaction);
        }
    }
}
