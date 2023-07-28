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

        public function __construct(\leantime\domain\repositories\reactions $reactionsRepo)
        {
            $this->reactionsRepo = $reactionsRepo;
        }

        /**
         * addReaction - adds a reaction to an entity, checks if a user has already reacted the same way
         * @access public
         *
         * @param string $module
         * @param int $moduleId
         * @param int $userId
         * @param string $reaction
         *
         * @return bool
         */
        public function addReaction(int $userId, string $module, int $moduleId, string $reaction): bool
        {
            if($module == '' || $moduleId == '' || $userId == '' || $reaction == ''){
                return false;
            }

            //Check if user already reacted in that category
            $userReactions = $this->getUserReactions($userId, $module, $moduleId);

            $currentReactionType = $this->getReactionType($reaction);

            foreach($userReactions as $previousReaction) {
                if($this->getReactionType($previousReaction['reaction']) == $currentReactionType) {
                    return false;
                }
            }

            return $this->reactionsRepo->addReaction($userId, $module, $moduleId, $reaction);
        }

        /**
         * getReactionType - returns the category/type of a given reaction
         * @access public
         *
         * @param string $reaction
         *
         * @return string|false
         */
        public function getReactionType($reaction): string|false {

            $types = \leantime\domain\models\reactions::getReactions();

            foreach($types as $reactionType => $reactionValues) {
                if(isset($reactionValues[$reaction])) {
                    return $reactionType;
                }
            }

            return false;

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
        public function getUserReactions(int $userId, string $module = '', ?int $moduleId = null, string $reaction = ''): array|false
        {

            return $this->reactionsRepo->getUserReactions($userId, $module, $moduleId, $reaction);
        }

        /**
         * addReaction - adds a reaction to an entity, checks if a user has already reacted the same way
         * @access public
         *
         * @param string $module
         * @param int $moduleId
         * @param int $userId
         * @param string $reaction
         *
         * @return bool
         */
        public function removeReaction(int $userId, string $module, int $moduleId, string $reaction): bool
        {
            return $this->reactionsRepo->removeUserReaction($userId, $module, $moduleId, $reaction);
        }
    }
}
