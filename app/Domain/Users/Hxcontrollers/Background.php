<?php

namespace Leantime\Domain\Users\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Users\Services\Backgrounds;
use Leantime\Views\Composers\Header;
use Symfony\Component\HttpFoundation\Response;

/**
 * The "Background" tab of the user's own settings: choose the color gradient, an uploaded
 * image or (when configured) an Unsplash photo as page background.
 *
 * Every change saves right away and answers with the "current background" fragment, which also
 * swaps the page's background style out of band, so the new background shows without a reload.
 * Changes only accept POST: the Frontcontroller dispatches these actions on GET too, and a GET
 * skips the request-origin check, so a cross-site link could otherwise reset the background.
 */
class Background extends HtmxController
{
    protected static string $view = 'users::partials.background';

    private Backgrounds $backgrounds;

    private Theme $theme;

    /**
     * Initialize the controller with dependencies.
     */
    public function init(Backgrounds $backgrounds, Theme $theme): void
    {
        $this->backgrounds = $backgrounds;
        $this->theme = $theme;
    }

    /**
     * The whole tab.
     */
    public function get(): void
    {
        $this->assignTabVariables();
    }

    /**
     * Unsplash search results for the "q" query. "page" is sent by the refresh button only, so a
     * changed query starts at page 1.
     */
    public function search(): string
    {
        $query = (string) $this->incomingRequest->query->get('q', '');
        $page = (int) $this->incomingRequest->query->get('page', 1);

        $searchResult = $this->backgrounds->searchUnsplash($query, $page);

        $this->assignTabVariables();
        $this->tpl->assign('photos', $searchResult['photos']);
        $this->tpl->assign('nextPage', $searchResult['nextPage']);

        return 'unsplash-results';
    }

    /**
     * Switch between gradient and image.
     */
    public function setType(): string|Response
    {
        if (! $this->incomingRequest->isMethod('POST')) {
            return $this->tpl->emptyResponse(405);
        }

        try {
            if ($this->backgrounds->setBackgroundType((string) $this->incomingRequest->request->get('type', ''))) {
                $this->tpl->setNotification(__('notifications.background_settings_saved'), 'success');
            } else {
                $this->tpl->setNotification(__('notifications.background_not_saved'), 'error');
            }
        } catch (ValidationException $exception) {
            $this->tpl->setNotification(__('notifications.background_not_saved'), 'error');
        }

        $this->assignTabVariables();

        return 'current-background';
    }

    /**
     * Use the Unsplash photo with the posted "photoId".
     */
    public function useUnsplashPhoto(): string|Response
    {
        if (! $this->incomingRequest->isMethod('POST')) {
            return $this->tpl->emptyResponse(405);
        }

        $photoId = (string) $this->incomingRequest->request->get('photoId', '');

        if ($this->backgrounds->useUnsplashPhoto($photoId)) {
            $this->tpl->setNotification(__('notifications.background_settings_saved'), 'success');
        } else {
            $this->tpl->setNotification(__('notifications.background_not_saved'), 'error');
        }

        $this->assignTabVariables();

        return 'current-background';
    }

    /**
     * Use the uploaded image ("file") as background.
     */
    public function upload(): string|Response
    {
        if (! $this->incomingRequest->isMethod('POST')) {
            return $this->tpl->emptyResponse(405);
        }

        if ($this->backgrounds->useUploadedImage($_FILES)) {
            $this->tpl->setNotification(__('notifications.background_settings_saved'), 'success');
        } else {
            $this->tpl->setNotification(__('notifications.background_upload_failed'), 'error');
        }

        $this->assignTabVariables();

        return 'current-background';
    }

    /**
     * Template variables of the whole tab. Blade renders the whole view even when only one
     * fragment is returned, so every action assigns all of them.
     */
    private function assignTabVariables(): void
    {
        $currentBackground = $this->backgrounds->getCurrentBackground();

        $this->tpl->assign('unsplashEnabled', $this->backgrounds->isUnsplashEnabled());
        $this->tpl->assign('backgroundType', $currentBackground['type']);
        $this->tpl->assign('backgroundImage', $currentBackground['image']);
        $this->tpl->assign('primaryColor', $this->theme->getPrimaryColor());
        $this->tpl->assign('secondaryColor', $this->theme->getSecondaryColor());

        // Same variables the page header uses for its background style (sections.backgroundStyle).
        $this->tpl->assign('themeType', $currentBackground['type']);
        $this->tpl->assign('themeBg', $currentBackground['image']);
        $this->tpl->assign('themeBgCss', Header::toCssString($currentBackground['image']));
        $this->tpl->assign('themeOpacity', $currentBackground['type'] === Backgrounds::TYPE_IMAGE ? 1 : 0.1);
    }
}
