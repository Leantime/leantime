<?php

namespace Leantime\Domain\Notifications\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\HtmxController;

class News extends HtmxController
{
    protected static string $view = 'notifications::partials.latestNews';

    private \Leantime\Domain\Notifications\Services\News $newsService;

    /**
     * Controller constructor
     */
    public function init(\Leantime\Domain\Notifications\Services\News $newsService): void
    {
        $this->newsService = $newsService;

    }

    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function get()
    {
        if (! env('LEAN_NEWS_ENABLED', true)) {
            $this->tpl->assign('rss', 'News service is disabled');

            return;
        }

        $news = false;
        try {
            $news = $this->newsService->getLatest(session('userdata.id'));

        } catch (\Exception $e) {
            Log::warning('Could not connect to news server');
        }

        if ($news === false) {
            $news = 'Could not connect to news server';
        }

        $this->tpl->assign('rss', $news);
    }
}
