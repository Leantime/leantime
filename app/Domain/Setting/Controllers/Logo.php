<?php

namespace Leantime\Domain\Setting\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the company logo upload.
 *
 * Relocated from the retired Api\Controllers\Setting so the behaviour lives in the
 * Setting domain. Reachable at the canonical /setting/logo and at the backward-compatible
 * /api/setting alias (registered in Setting/routes.php) used by the company-settings
 * logo cropper. The 501 GET/PATCH/DELETE stubs from the old controller are not carried over.
 *
 * The company logo is a global setting, so the upload now requires admin+ — the legacy
 * endpoint had no role check, letting any authenticated user overwrite it.
 */
class Logo extends Controller
{
    private SettingService $settingService;

    public function init(SettingService $settingService): void
    {
        $this->settingService = $settingService;
    }

    /**
     * POST — store an uploaded company logo (multipart field "file").
     */
    public function post(array $params): Response
    {
        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->displayJson(['status' => 'unauthorized'], 403);
        }

        if (! isset($_FILES['file'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        $_FILES['file']['name'] = 'logo.png';

        $this->settingService->setLogo($_FILES);

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return $this->tpl->displayJson(['status' => 'ok']);
    }
}
