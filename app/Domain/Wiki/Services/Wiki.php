<?php

namespace Leantime\Domain\Wiki\Services {

    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;
    class Wiki
    {
        private $wikiRepository;

        public function __construct(WikiRepository $wikiRepository)
        {
            $this->wikiRepository = $wikiRepository;
        }

        public function getArticle($id, $projectId = null)
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

        public function getAllProjectWikis($projectId)
        {
            return $this->wikiRepository->getAllProjectWikis($projectId);
        }

        public function getAllWikiHeadlines($wikiId, $userId)
        {
            return $this->wikiRepository->getAllWikiHeadlines($wikiId, $userId);
        }

        public function getWiki($id)
        {
            return $this->wikiRepository->getWiki($id);
        }

        public function createWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki)
        {
            return $this->wikiRepository->createWiki($wiki);
        }

        public function updateWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki, $wikiId)
        {
            return $this->wikiRepository->updateWiki($wiki, $wikiId);
        }

        public function createArticle(\Leantime\Domain\Wiki\Models\Article $article)
        {
            return $this->wikiRepository->createArticle($article);
        }

        public function updateArticle(\Leantime\Domain\Wiki\Models\Article $article)
        {
            return $this->wikiRepository->updateArticle($article);
        }
    }

}
