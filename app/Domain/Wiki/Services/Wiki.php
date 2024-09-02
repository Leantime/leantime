<?php

namespace Leantime\Domain\Wiki\Services {

    use Leantime\Domain\Wiki\Models\Article;
    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

    /**
     *
     *
     * @api
     */
    class Wiki
    {
        private WikiRepository $wikiRepository;

        /**
         * @param WikiRepository $wikiRepository
         *
        *
        */
        public function __construct(WikiRepository $wikiRepository)
        {
            $this->wikiRepository = $wikiRepository;
        }

        /**
         * @param $id
         * @param $projectId
         * @return mixed
         *
     * @api
     */
        public function getArticle($id, $projectId = null): mixed
        {

            if (!is_null($id)) {
                $article = $this->wikiRepository->getArticle($id, $projectId);

                if (!$article) {
                    $article = $this->wikiRepository->getArticle(-1, $projectId);
                }
            } else {
                $article = $this->wikiRepository->getArticle(-1, $projectId);
            }


            return $article;
        }

        /**
         * @param $projectId
         * @return array|false
         *
     * @api
     */
        public function getAllProjectWikis($projectId): array|false
        {
            return $this->wikiRepository->getAllProjectWikis($projectId);
        }

        /**
         * @param $wikiId
         * @param $userId
         * @return array|false
         *
     * @api
     */
        public function getAllWikiHeadlines($wikiId, $userId): false|array
        {
            return $this->wikiRepository->getAllWikiHeadlines($wikiId, $userId);
        }

        /**
         * @param $id
         * @return mixed
         *
     * @api
     */
        public function getWiki($id): mixed
        {
            return $this->wikiRepository->getWiki($id);
        }

        /**
         * @param \Leantime\Domain\Wiki\Models\Wiki $wiki
         * @return false|string
         *
     * @api
     */
        public function createWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki): false|string
        {
            return $this->wikiRepository->createWiki($wiki);
        }

        /**
         * @param \Leantime\Domain\Wiki\Models\Wiki $wiki
         * @param $wikiId
         * @return bool
         *
     * @api
     */
        public function updateWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki, $wikiId): bool
        {
            return $this->wikiRepository->updateWiki($wiki, $wikiId);
        }

        /**
         * @param Article $article
         * @return false|string
         *
     * @api
     */
        public function createArticle(Article $article): false|string
        {
            return $this->wikiRepository->createArticle($article);
        }

        /**
         * @param Article $article
         * @return bool
         *
     * @api
     */
        public function updateArticle(Article $article): bool
        {
            return $this->wikiRepository->updateArticle($article);
        }
    }

}
