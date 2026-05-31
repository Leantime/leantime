<?php

namespace Leantime\Domain\Users\Controllers;

use Illuminate\Http\Request;
use Leantime\Core\Http\Responses\ImageResponse;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves and updates user profile images.
 *
 * A native Laravel controller (constructor DI, route-bound actions returning a
 * Response/Responsable) — no Frontcontroller/legacy base-controller machinery.
 * Relocated from the retired Api\Controllers\Users. Bound in Users/routes.php at the
 * canonical /users/profileImage/{id} plus the backward-compatible /api/users alias that
 * core templates and the plugin submodule still reference.
 */
class ProfileImage
{
    public function __construct(private UserService $userService) {}

    /**
     * GET — returns the profile image as a binary/SVG response.
     *
     * The id comes from the canonical path segment ({id}) or the legacy ?profileImage=
     * query param. "me" resolves to the session user; "false"/empty yields the placeholder.
     */
    public function show(Request $request, ?string $id = null): ImageResponse
    {
        $id = $request->query('profileImage', $id) ?? 'false';

        if ($id === 'me') {
            $id = session('userdata.id');
        }

        return new ImageResponse($this->userService->getProfilePicture($id));
    }

    /**
     * POST — uploads a new profile photo for the current user.
     *
     * The target is always the session user; a client-supplied id is never trusted.
     */
    public function upload(): Response
    {
        if (! isset($_FILES['file'])) {
            return response()->json(['error' => 'File not included'], 400);
        }

        $_FILES['file']['name'] = 'userPicture.png';

        $this->userService->setProfilePicture($_FILES, session('userdata.id'));

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return response()->json(['status' => 'ok']);
    }
}
