<?php

namespace Leantime\Domain\Reactions\Services {

    /**
     *
     *
     * @api
     */
    class Reactions
    {
        /**
         * @access private
         * @var    \Leantime\Domain\Reactions\Repositories\Reactions $reactionsRepo reactions repository
         *
     * @api
     */
        private \Leantime\Domain\Reactions\Repositories\Reactions $reactionsRepo;

        /**
         * @param \Leantime\Domain\Reactions\Repositories\Reactions $reactionsRepo
         *
     */
        public function __construct(\Leantime\Domain\Reactions\Repositories\Reactions $reactionsRepo)
        {
            $this->reactionsRepo = $reactionsRepo;
        }

        /**
         * addReaction - adds a reaction to an entity, checks if a user has already reacted the same way
         * @access public
         *
         * @param string $module
         * @param int    $moduleId
         * @param int    $userId
         * @param string $reaction
         *
         * @return bool
         *
     * @api
     */
        public function addReaction(int $userId, string $module, int $moduleId, string $reaction): bool
        {
            if ($module == '' || $moduleId == '' || $userId == '' || $reaction == '') {
                return false;
            }

            //Check if user already reacted in that category
            $userReactions = $this->getUserReactions($userId, $module, $moduleId);

            $currentReactionType = $this->getReactionType($reaction);

            foreach ($userReactions as $previousReaction) {
                if ($this->getReactionType($previousReaction['reaction']) == $currentReactionType) {
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
         *
     * @api
     */
        public function getReactionType(string $reaction): string|false
        {

            $types = \Leantime\Domain\Reactions\Models\Reactions::getReactions();

            foreach ($types as $reactionType => $reactionValues) {
                if (isset($reactionValues[$reaction])) {
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
         * @param int    $moduleId
         *
         * @return array|bool returns the array on success or false on failure
         *
     * @api
     */
        public function getGroupedEntityReactions(string $module, int $moduleId): array|false
        {
            return $this->reactionsRepo->getGroupedEntityReactions($module, $moduleId);
        }

        /**
         * getMyReactions - gets user reactions. Can be very broad or very targeted
         * @access public
         *
         * @param int      $userId
         * @param string   $module
         * @param int|null $moduleId
         * @param string   $reaction
         *
         * @return array|false
         *
     * @api
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
         * @param int    $moduleId
         * @param int    $userId
         * @param string $reaction
         *
         * @return bool
         *
     * @api
     */
        public function removeReaction(int $userId, string $module, int $moduleId, string $reaction): bool
        {
            return $this->reactionsRepo->removeUserReaction($userId, $module, $moduleId, $reaction);
        }
    }
}
