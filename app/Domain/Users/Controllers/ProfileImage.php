<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Support\ImageResponse;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves and updates user profile images.
 *
 * Relocated from the retired Api\Controllers\Users so the behaviour lives in the
 * Users domain. Reachable at the canonical /users/profileImage/{id} and at the
 * backward-compatible /api/users alias (registered in Users/routes.php) that the
 * many <img src> references across core templates and the plugin submodule still use.
 */
class ProfileImage extends Controller
{
    private UserService $userService;

    public function init(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * GET — returns the profile image as a binary/SVG response.
     *
     * Accepts the id from the canonical path segment ({id}) or the legacy
     * ?profileImage= query param. The special value "me" resolves to the session
     * user; "false"/empty yields the generated placeholder avatar.
     */
    public function get(array $params): Response
    {
        $id = $params['profileImage'] ?? $params['id'] ?? 'false';

        if ($id === 'me') {
            $id = session('userdata.id');
        }

        return ImageResponse::make($this->userService->getProfilePicture($id));
    }

    /**
     * POST — uploads a new profile photo for the current user.
     *
     * The target id is always the session user; a client-supplied id is never
     * trusted (the service setter trusts the id it is given).
     */
    public function post(array $params): Response
    {
        if (! isset($_FILES['file'])) {
            return $this->tpl->displayJson(['error' => 'File not included'], 400);
        }

        $_FILES['file']['name'] = 'userPicture.png';

        $this->userService->setProfilePicture($_FILES, session('userdata.id'));

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return $this->tpl->displayJson(['status' => 'ok']);
    }
}
