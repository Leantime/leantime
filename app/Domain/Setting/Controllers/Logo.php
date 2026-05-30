<?php

namespace Leantime\Domain\Setting\Controllers;

use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the company logo upload.
 *
 * A native Laravel controller (constructor DI, route-bound action). Relocated from the
 * retired Api\Controllers\Setting. Bound in Setting/routes.php at the canonical
 * /setting/logo plus the backward-compatible /api/setting alias used by the company-settings
 * logo cropper. The 501 GET/PATCH/DELETE stubs from the old controller are not carried over.
 *
 * The company logo is a global setting, so the upload now requires admin+ — the legacy
 * endpoint had no role check, letting any authenticated user overwrite it.
 */
class Logo
{
    public function __construct(private SettingService $settingService) {}

    /**
     * POST — store an uploaded company logo (multipart field "file").
     */
    public function post(): Response
    {
        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return response()->json(['status' => 'unauthorized'], 403);
        }

        if (! isset($_FILES['file'])) {
            return response()->json(['status' => 'failure'], 400);
        }

        $_FILES['file']['name'] = 'logo.png';

        $this->settingService->setLogo($_FILES);

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return response()->json(['status' => 'ok']);
    }
}
