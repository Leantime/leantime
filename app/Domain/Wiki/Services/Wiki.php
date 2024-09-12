<?php

namespace Leantime\Domain\Wiki\Services {

    use Leantime\Domain\Wiki\Models\Article;
    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

    /**
     * @api
     */
    class Wiki
    {
        private WikiRepository $wikiRepository;

        public function __construct(WikiRepository $wikiRepository)
        {
            $this->wikiRepository = $wikiRepository;
        }

        /**
         * @api
         */
        public function getArticle($id, $projectId = null): mixed
        {

            if (! is_null($id)) {
                $article = $this->wikiRepository->getArticle($id, $projectId);

                if (! $article) {
                    $article = $this->wikiRepository->getArticle(-1, $projectId);
                }
            } else {
                $article = $this->wikiRepository->getArticle(-1, $projectId);
            }

            return $article;
        }

        /**
         * @api
         */
        public function getAllProjectWikis($projectId): array|false
        {
            return $this->wikiRepository->getAllProjectWikis($projectId);
        }

        /**
         * @api
         */
        public function getAllWikiHeadlines($wikiId, $userId): false|array
        {
            return $this->wikiRepository->getAllWikiHeadlines($wikiId, $userId);
        }

        /**
         * @api
         */
        public function getWiki($id): mixed
        {
            return $this->wikiRepository->getWiki($id);
        }

        /**
         * @api
         */
        public function createWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki): false|string
        {
            return $this->wikiRepository->createWiki($wiki);
        }

        /**
         * @api
         */
        public function updateWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki, $wikiId): bool
        {
            return $this->wikiRepository->updateWiki($wiki, $wikiId);
        }

        /**
         * @api
         */
        public function createArticle(Article $article): false|string
        {
            return $this->wikiRepository->createArticle($article);
        }

        /**
         * @api
         */
        public function updateArticle(Article $article): bool
        {
            return $this->wikiRepository->updateArticle($article);
        }
    }

}
