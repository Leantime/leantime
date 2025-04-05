<?php

namespace Leantime\Domain\Wiki\Services;

use Leantime\Core\Language;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

/**
 * @api
 */
class Wiki
{
    private WikiRepository $wikiRepository;

    private Language $language;

    public function __construct(WikiRepository $wikiRepository,
        Language $language)
    {
        $this->wikiRepository = $wikiRepository;
        $this->language = $language;
    }

    /**
     * @api
     */
    public function getArticle($id, $projectId = null): mixed
    {

        if ($projectId == null) {
            $projectId = session('currentProject');
        }

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
     * Gets all project wikis. Creates one if there aren't any
     *
     *
     * @api
     */
    public function getAllProjectWikis($projectId): array|false
    {

        $wikis = $this->wikiRepository->getAllProjectWikis($projectId);

        if (! $wikis || count($wikis) == 0) {

            $wiki = app()->make(\Leantime\Domain\Wiki\Models\Wiki::class);
            $wiki->title = $this->language->__('label.default');
            $wiki->projectId = $projectId;
            $wiki->author = session('userdata.id');

            $id = $this->createWiki($wiki);
            $wikis = $this->wikiRepository->getAllProjectWikis($projectId);
        }

        return $wikis;
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

        $wikiId = $this->wikiRepository->createWiki($wiki);

        $this->setCurrentWiki($wikiId);

        return $wikiId;
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

    public function setCurrentWiki($id)
    {

        // Clear cache
        $this->clearWikiCache();
        $wiki = $this->getWiki($id);

        if ($wiki) {
            // Set the session
            session(['currentWiki' => $id]);

            return true;
        }

        return false;

    }

    public function setCurrentArticle($id, $userId)
    {

        $currentArticle = $this->getArticle($id);

        if ($currentArticle && $currentArticle->id != null) {
            session(['currentWiki' => $currentArticle->canvasId]);
            session(['lastArticle' => $currentArticle->id]);

            return $currentArticle;
        }

        return false;

    }

    public function getDefaultArticleForWiki($wikiId, $userId)
    {

        $wikiHeadlines = $this->getAllWikiHeadlines(
            $wikiId,
            $userId
        );

        if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
            $currentArticle = $this->getArticle(
                $wikiHeadlines[0]->id
            );

            return $currentArticle;
        }

        return false;

    }

    public function clearWikiCache()
    {

        session()->forget('lastArticle');
        session()->forget('currentWiki');

    }
}
